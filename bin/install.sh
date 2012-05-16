#!/bin/sh

echo "please set db username & password"
echo "please set superuser password"
echo "please set media_dir"
echo "please set RewriteBase in .htaccess"


mkdir ../files
mkdir ../files/log
mkdir ../files/cache
mkdir ../files/media
chgrp -R apache ../files/*
chmod -R g+w ../files/*

echo "creating tables"
php create_tables.php

echo "generating model classes"
php class_gen.php

echo
echo "framework setup complete"
echo
