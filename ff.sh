#!/bin/bash
FFPATH=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
FFPHP="${FFPATH}/cli-launch.php"
FFTMP="${FFPATH}/cli-launch.tmp"

# Run fast-forward and capture output in $FFTMP
php $FFPHP "$@" | tee $FFTMP

# Get returned commands
retcmd=$(\grep -E "^cmd:.+" $FFTMP | sed 's/^cmd:\(.*\)/\1/')

# Execute commands
eval $retcmd
