#!/bin/bash

cd /home/imal/public_html/

git commit -a -m "Auto-Commit"
git remote add origin "https://github.com/imalpasha/imal_post_hook"
git remote -v
git push origin master