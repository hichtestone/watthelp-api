# don't know why it still take var/cache dir even excluded in conf
rm -Rf var/cache/*
vendor/bin/php-cs-fixer fix . --config=.php_cs --dry-run
# $? catches the last exit code, which is the one we want to exit in the end of the day
return=$?
#we run it twice (first to get exit code, second to get the patch.diff file to share in artifact)
vendor/bin/php-cs-fixer fix . --config=.php_cs
[ -d var ] || mkdir var
git diff > var/patch.diff

if [ -s var/patch.diff ]
then
    cat var/patch.diff
else
    echo "Return code : $return but patch file is empty... Doing nothing"
    rm -f var/patch.diff
    return=0
fi

exit $return
