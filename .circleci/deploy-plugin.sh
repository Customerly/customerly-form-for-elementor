#!/usr/bin/env bash

if [[ -z "$CIRCLECI" ]]; then
    echo "This script can only be run by CircleCI. Aborting." 1>&2
    exit 1
fi

if [[ -z "$CIRCLE_BRANCH" || "$CIRCLE_BRANCH" != "master" ]]; then
    echo "Build branch is required and must be 'master' branch. Stopping deployment." 1>&2
    exit 0
fi

if [[ -z "$WP_SVN_PASSWORD" ]]; then
    echo "WordPress.org password not set. Aborting." 1>&2
    exit 1
fi

if [[ -z "$WP_SVN_USERNAME" ]]; then
    echo "WordPress.org username not set. Aborting." 1>&2
    exit 1
fi

PLUGIN_SVN_PATH="/tmp/svn"

# Remove the "v" at the beginning of the git tag
SVN_TAG=$(grep -oP '(?<=Stable tag: )([0-9]+\.[0-9]+(\.[0-9])*)' ./trunk/readme.txt)
echo "Get tag from readme.txt: $SVN_TAG"

# Check if the latest SVN tag exists already
#TAG=$(svn ls "https://plugins.svn.wordpress.org/email-marketing-and-crm-for-elementor-by-customerly/tags/$SVN_TAG")
#error=$?
#if [ $error == 0 ]; then
#    # Tag exists, don't deploy
#    echo "Latest tag ($SVN_TAG) already exists on the WordPress directory. No deployment needed!"
#    exit 0
#fi

# Checkout the SVN repo
svn co -q "http://plugins.svn.wordpress.org/email-marketing-and-crm-for-elementor-by-customerly" $PLUGIN_SVN_PATH

# Delete the trunk directory
rm -rf $PLUGIN_SVN_PATH/trunk

# Copy our new version of the plugin as the new trunk directory
cp -r ./trunk $PLUGIN_SVN_PATH/trunk

# Copy our new version of the plugin into new version tag directory
cp -r ./trunk $PLUGIN_SVN_PATH/tags/$SVN_TAG

# Move to SVN directory
cd $PLUGIN_SVN_PATH

# Add new files to SVN
svn stat | grep '^?' | awk '{print $2}' | xargs -I x svn add x@

# Remove deleted files from SVN
svn stat | grep '^!' | awk '{print $2}' | xargs -I x svn rm --force x@

# Commit to SVN
svn ci --no-auth-cache --username $WP_SVN_USERNAME --password $WP_SVN_PASSWORD -m "Deploy version $SVN_TAG"