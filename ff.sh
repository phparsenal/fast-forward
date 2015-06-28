#!/bin/bash
FFPATH="/home/amblin/dev/fast-forward/"
FFPHP="${FFPATH}cli-launch.php"
FFTMP="${FFPATH}cli-launch.tmp"

# Run fast-forward and capture output in $FFTMP
php $FFPHP "$@" | tee $FFTMP

# Get returned commands
retcmd=$(\grep -E "^cmd:.+" $FFTMP | sed 's/^cmd:\(.*\)/\1/')

# Execute commands
eval $retcmd
