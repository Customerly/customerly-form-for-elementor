#!/usr/bin/env bash

if [[ -z "$CIRCLECI" ]]; then
    echo "This script can only be run by CircleCI. Aborting." 1>&2
    exit 1
fi

if [[ -z "$CIRCLE_BRANCH" || "$CIRCLE_BRANCH" != "master" ]]; then
    echo "Build branch is required and must be 'master' branch. Stopping deployment." 1>&2
    exit 0
fi

if [[ -z "$WP_SVN_USERNAME" ]]; then
    echo "WordPress.org username not set. Aborting." 1>&2
    exit 1
fi

if [[ -z "$WP_SVN_PASSWORD" ]]; then
    echo "WordPress.org password not set. Aborting." 1>&2
    exit 1
fi

PLUGIN_SVN_PATH="/tmp/svn"

# Checkout the SVN repo
svn co -q "http://plugins.svn.wordpress.org/email-marketing-and-crm-for-elementor-by-customerly" $PLUGIN_SVN_PATH

# Delete the assets directory
rm -rf $PLUGIN_SVN_PATH/assets

# Copy our plugin assets as the new assets directory
cp -r ./assets $PLUGIN_SVN_PATH/assets

# Move to SVN directory
cd $PLUGIN_SVN_PATH

# Add new files to SVN
svn stat | grep '^?' | awk '{print $2}' | xargs -I x svn add x@

# Remove deleted files from SVN
svn stat | grep '^!' | awk '{print $2}' | xargs -I x svn rm --force x@

# Commit to SVN
svn ci --no-auth-cache --username $WP_SVN_USERNAME --password $WP_SVN_PASSWORD -m "$GIT_COMMIT_MESSAGE"
