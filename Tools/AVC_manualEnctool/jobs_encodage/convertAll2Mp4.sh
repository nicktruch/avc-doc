#!/bin/bash
# Script which converts a video file into .mp4 format
# First argument: the video folder
# Second argument: the input video filename (with extension)
# Third argument: the output video name (without extension)
# Four argument: the script to calculate padding
# Fifth argument: true for ipod video format. false for hd.

WIDTH=1280
HEIGHT=720
# ubuntu VPRE="-vpre medium"
VPRE="-preset medium"
ASPECT="-aspect 16:9"
# ubuntu AUDIO="-acodec libvo_aacenc"
AUDIO="-acodec aac -strict -2"
SUFFNAME=""

#for ipod
if [ $5 == 'true' ]
then 
	WIDTH=640
	HEIGHT=480
	# ubuntu VPRE="-vpre medium -vpre ipod640"
	VPRE="-preset medium -profile:v baseline -level 3.0 -maxrate 10000000 -bufsize 10000000"
	ASPECT="-aspect 4:3"
	SUFFNAME="_ipod"
fi

# Calculate padding
donnees=`bash $4 $1 $2 $WIDTH $HEIGHT`
L=`echo $donnees | cut -d: -f1`
H=`echo $donnees | cut -d: -f2`
PL=`echo $donnees | cut -d: -f3`
PHB=`echo $donnees | cut -d: -f4`

cd $1

# test if no sound
if [ `stat -c '%s' $3.mp3` -eq 0 ]
then
	AUDIO=""
fi

#Creation du fichier HD mp4
# ubuntu /usr/bin/ffmpeg -v -1 -i "$2" $AUDIO -r 25 -vcodec libx264 -s "$L"x"$H" -vf "pad=$WIDTH:$HEIGHT:$PL:$PHB:black" $ASPECT $VPRE -crf 27 -g 100 -threads 0 -y $3_tmp.mp4 &> /dev/null
/usr/bin/ffmpeg -v -1 -i "$2" $AUDIO -r 25 -vcodec libx264 -vf scale="$L":"$H",pad="$WIDTH:$HEIGHT:$PL:$PHB:black" $ASPECT $VPRE -crf 27 -g 100 -threads 0 -y $3_tmp.mp4 &> /dev/null
/usr/bin/qt-faststart $3_tmp.mp4 "$3""$SUFFNAME".mp4 &> /dev/null


#suppression des fichiers temporaires
rm $3_tmp.mp4 &> /dev/null

