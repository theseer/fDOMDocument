#!/bin/sh
rm -f fDOMDocument*.tgz
mkdir -p tmp/TheSeer/fDOMDocument
cp -r src/* tmp/TheSeer/fDOMDocument
phpab -o tmp/TheSeer/fDOMDocument/autoload.php -b src src
cp package.xml tmp
cd tmp
pear package
mv fDOMDocument*.tgz ..
cd ..
rm -rf tmp
