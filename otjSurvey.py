__author__ = 'joe.snyder'
import argparse
import os
import zipfile
import shutil
import re
import subprocess
import sys

OTJ_SURVEY_EXPRESSIONS = (("Do not [Re]*distribute",[]),
  ("Copyright [0-9\-]*",[]),
  ("(Released|Licensed) ",[]),
  ("All rights reserved",[]),
  ("Deriv[atived]+",[])
)

OTJ_FILE_EXCLUDES = ("dat","dll", "DAT","exe", "EXE", "mdf","pdb","class")
OTJ_UNZIP_EXCLUDES= ("EAP","dll")
OTJ_SURVEY_HISTORY = []
global seven_zip

def remove_readonly(func, path, excinfo):
    os.chmod(path, stat.S_IWRITE)
    func(path)

def searchArchive(filepath,unzipPath, tmpDir):
    if os.path.isdir(unzipPath):
        unzipPath += "_1"
    if not (filepath.endswith(OTJ_UNZIP_EXCLUDES)):
        cmd=[seven_zip,"x","-bd", "-y", "-o"+unzipPath, filepath]
        ret = subprocess.call(cmd)
        if os.path.isdir(unzipPath):
            OTJ_SURVEY_HISTORY.append(filepath)
            parseSubmission(unzipPath.replace("\\\\?\\",""),tmpDir)
            OTJ_SURVEY_HISTORY.pop()
            shutil.rmtree(unzipPath.replace("\\\\?\\",""))

def parseSubmission(inputPath,tmpDir):
    # Read in file type
    for fileName in os.listdir(inputPath):
          filepath=''
          unzipPath=''
          if os.name == 'nt':
            filepath = "\\\\?\\"
            unzipPath= "\\\\?\\"
          filepath  += os.path.join(os.path.relpath(inputPath), fileName)
          splitVal = fileName.rfind('.')
          if splitVal:
            fileName = fileName[:splitVal]
          unzipPath += os.path.join(os.path.abspath(tmpDir),fileName.replace(" ","_"))
          print "Processing file: %s" % filepath
          if (zipfile.is_zipfile(filepath)) or filepath.endswith("7z"):
            searchArchive(filepath, unzipPath, tmpDir)
          elif os.path.isdir(filepath):
            parseSubmission(filepath,tmpDir)
          else:
            if not (filepath.endswith(OTJ_FILE_EXCLUDES)):
              outline= filepath
              if len(OTJ_SURVEY_HISTORY):
                 outline += "  from (" + OTJ_SURVEY_HISTORY[-1]+")"
              for line in open(filepath, "r"):
                for entry in OTJ_SURVEY_EXPRESSIONS:
                  if re.search(entry[0], line, re.I):
                    entry[1].append((outline,line))


def main():
    global seven_zip
    parser = argparse.ArgumentParser(description='OTJ Submission Analyzer')
    parser.add_argument('-p', '--paths', required=True, action='append',
        help='input file path to the directory that contains the OTJ submission to check')
    parser.add_argument('-d', '--dir', required=True,
        help='a file path where the script will attempt to unzip any archive files and will write any output files')
    parser.add_argument('-z', '--zip', required=True,
        help='a file path to the 7zip executable')
    result = parser.parse_args()
    seven_zip = result.zip
    print seven_zip
    try:
      for path in result.paths:
        parseSubmission(path, result.dir)
    finally:
        with open(os.path.join(result.dir, "SurveyResult.txt"),"w") as outfile:
          outfile.write("Begin Report")
          for entry in OTJ_SURVEY_EXPRESSIONS:
            if len(entry[1]):
              outfile.write("**********************\nFound results for\n")
              outfile.write(entry[0]+"\n")
              outfile.write("**********************\n")
              for item in entry[1]:
                outfile.write(item[0]+"\n     "+ item[1]+"\n")
            outfile.write("\n\n")
          outfile.write("End Report")
          outfile.close()


if __name__ == '__main__':
  main()
