#!/bin/sh
rm -rf web/texts
cp -R src/Resources/texts web/
rm web/texts/*/*.md
rm web/texts/*/*.yml
