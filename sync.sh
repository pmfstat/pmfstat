#!/bin/sh
if test -n "${PMF_FTP_HOST}"; then
    PMF_FTP_HOST="phpmyfaq.net";
fi

if test -n "${PMF_FTP_USER}" && test -n "${PMF_FTP_PATH}"; then
    echo -n "Please enter the password: "
    for i in `git-ls-files`
        do echo "put -O $PMF_FTP_PATH $i"
    done | lftp -u $PMF_FTP_USER $PMF_FTP_HOST
else
    echo "Please set PMF_FTP_USER and PMF_FTP_PATH!"
fi
