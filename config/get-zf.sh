#!/bin/sh
wget -O - http://framework.zend.com/releases/ZendFramework-1.10.2/ZendFramework-1.10.2-minimal.tar.gz | tar -C library -xvz --file=- ZendFramework-1.10.2-minimal/library
mv library/ZendFramework-1.10.2-minimal/library/Zend/ library/Zend
rm -rf library/ZendFramework-1.10.2-minimal

