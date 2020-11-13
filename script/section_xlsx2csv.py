import sys
import re
from openpyxl import load_workbook

if len(sys.argv) != 3:
    sys.exit("Usage: python " + sys.argv[0] + " input output")

input_file = sys.argv[1]
output_file = sys.argv[2]

wb = load_workbook(input_file)

f = open(output_file, "w")

for ws in wb._sheets:
    for row in ws.iter_rows(values_only=True):
        if type(row[0]) == unicode or type(row[0]) == str:
            if re.match(r'\d{4} +\w{2,4} +\d{3} +\d{2} +.+', row[0]):
                f.write(re.sub(r'\s+', '\t', row[0], 4) + '\n')

f.close()
