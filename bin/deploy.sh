#! /bin/bash
#
# Script to deploy from Github to WordPress.org Plugin Repository
# A modification of Ben Balter's deploy script as found here: https://github.com/benbalter/Github-to-WordPress-Plugin-Directory-Deployment-Script
# Which is a modification of Dean Clatworthy's deploy script as found here: https://github.com/deanc/wordpress-plugin-git-svn
# The difference is that this script lives in the plugin's git repo & doesn't require an existing SVN repo.
# Source: https://github.com/thenbrent/multisite-user-management/blob/master/deploy.sh

# main config, set off of plugin slug
PLUGINSLUG='post-forking'
CURRENTDIR=`pwd`
MAINFILE="$PLUGINSLUG.php" # this should be the name of your main php file in the wordpress plugin

# git config
GITPATH="$CURRENTDIR/" # this file should be in the base of your git repository

# svn config
SVNPATH="/tmp/$PLUGINSLUG" # path to a temp SVN repo. No trailing slash required and don't add trunk.
SVNURL="http://plugins.svn.wordpress.org/$PLUGINSLUG/" # Remote SVN repo on WordPress.org, with no trailing slash
SVNUSER="jorbin" # your svn username

# Let's begin...
echo ".........................................."
echo 
echo "Preparing to deploy Post Forking"
echo 
echo ".........................................."
echo 

# Check version in readme.txt is the same as plugin file
NEWVERSION1=`grep "^Stable tag" $GITPATH/readme.txt | awk -F' ' '{print $3}'`
echo "readme version: $NEWVERSION1"
NEWVERSION2=`grep "^Version" $GITPATH/$MAINFILE | awk -F' ' '{print $2}'`
echo "$MAINFILE version: $NEWVERSION2"

if [ "$NEWVERSION1" != "$NEWVERSION2" ]; then echo "Versions don't match. Exiting...."; exit 1; fi

    echo "Versions match in readme.txt and PHP file. Let's proceed..."

    cd $GITPATH
    echo -e "Enter a commit message for this new version: \c"
    read COMMITMSG
    git commit -am "$COMMITMSG"

    echo "Tagging new version in git"
    git tag -a "$NEWVERSION1" -m "Tagging version $NEWVERSION1"

    echo "Pushing latest commit to origin, with tags"
    git push origin master
    git push origin master --tags

    echo 
    echo "Creating local copy of SVN repo ..."
    svn co $SVNURL $SVNPATH

    echo "Ignoring github specific files and deployment script"
    svn propset svn:ignore "deploy.sh
    bin/
    Gruntfile.js
    readme.md
    phpunit.xml
    package.json
    tests/
    .git
    .gitignore" "$SVNPATH/trunk/"

#export git -> SVN
echo "Exporting the HEAD of master from git to the trunk of SVN"
git checkout-index -a -f --prefix=$SVNPATH/trunk/

#if submodule exist, recursively check out their indexes
if [ -f ".gitmodules" ]
    then
    echo "Exporting the HEAD of each submodule from git to the trunk of SVN"
    git submodule init
    git submodule update
    git submodule foreach --recursive 'git checkout-index -a -f --prefix=$SVNPATH/trunk/$path/'
    fi

    echo "Changing directory to SVN and committing to trunk"
    cd $SVNPATH/trunk/
# Add all new files that are not set to be ignored
    svn status | grep -v "^.[ \t]*\..*" | grep "^?" | awk '{print $2}' | xargs svn add
    svn commit --username=$SVNUSER -m "$COMMITMSG"

    echo "Creating new SVN tag & committing it"
    cd $SVNPATH
    svn copy trunk/ tags/$NEWVERSION1/
    cd $SVNPATH/tags/$NEWVERSION1
    svn commit --username=$SVNUSER -m "Tagging version $NEWVERSION1"

    echo "Removing temporary directory $SVNPATH"
    rm -fr $SVNPATH/

    echo "*** FIN ***"
