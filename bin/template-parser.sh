#!/bin/bash
#
# MIT License
#
# Copyright (c) 2024 DOVU Global Limited
#
# Permission is hereby granted, free of charge, to any person obtaining a copy
# of this software and associated documentation files (the "Software"), to deal
# in the Software without restriction, including without limitation the rights
# to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
# copies of the Software, and to permit persons to whom the Software is
# furnished to do so, subject to the following conditions:
#
# The above copyright notice and this permission notice shall be included in all
# copies or substantial portions of the Software.
#
# THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
# IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
# FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
# AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
# LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
# OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
# SOFTWARE.

# ANSI color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to display usage information
usage() {
    echo -e "${YELLOW}"
    echo "Usage: $0 <path-to-edn-file>"
    echo
    echo "This command enables the conversion of DovuOS or Paladin blueprint configurations"
    echo "into JSON so that it can be passed into the workflow engine for creation and processing."
    echo -e "${NC}"
    exit 1
}

# Check help function with usage
if [ "$1" == "--help" ] || [ "$1" == "-h" ]; then
    usage
fi

# Assign the first argument to the variable path
path=$1

# Check if the file exists
if [ ! -f "$path" ]; then
    echo -e "${RED}Error: File '$path' not found!${NC}"
    exit 1
fi

# Check if jet is available, if not install it
if ! command -v jet &> /dev/null
then
    echo -e "${YELLOW}'jet' command for configuration parsing could not be found${NC}"
    echo -e "${YELLOW}*************** INSTALLING JET ***************${NC}"
    bash <(curl -s https://raw.githubusercontent.com/borkdude/jet/master/install)
    echo -e "${GREEN}*************** INSTALLED JET ***************${NC}"
    if ! command -v jet &> /dev/null
    then
        echo -e "${RED}Error: jet command installation failed!${NC}"
        exit 1
    fi
fi

# Check if the correct number of arguments is provided
if [ $# -ne 1 ]; then
    usage
fi

# Convert the EDN file to JSON using Jet
echo | cat "$path" | jet --to json
