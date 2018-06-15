#!/usr/bin/env bash

####################################
#            CONFIG
####################################

# Abort if anything fails
set -e

# Get our current path
SCRIPTPATH=$( cd $(dirname $0) ; pwd)
# Current user
CURRENT_USER="$(whoami)"
# get branch from commit, otherwise assume staging
TARGET_BRANCH=${1:-'8.x-3.x'}
# set the env
TARGET_ENV="dev"
if [ $TARGET_BRANCH != "8.x-3.x" ]; then
  TARGET_ENV="stage"
fi
# Repository root
REPO_ROOT="/home/deploy/www/$TARGET_ENV"

####################################
#           Deploy
####################################

# Make sure we're in the right directory
echo "=== Changing to $REPO_ROOT ==="
cd $REPO_ROOT

# Get latest git changes from branch
echo "=== Grabbing git changes ==="

# check changes to tracked
git diff --quiet || { echo "*** ERROR: Local changes found! ***"; exit 1; }

# all good, get new stuff
git fetch origin
git pull
git reset --hard $TARGET_BRANCH

# Make sure that the composer exists
echo "=== Checking for composer ==="
command -v composer >/dev/null 2>&1 || {
  echo >&2 "*** composer does not appear to be available. Unable to deploy ***";
  exit 1;
}
echo "=== Running Composer Install ==="
composer install --no-interaction --optimize-autoloader

# Run installation
echo "=== Install Drupal ==="
cd $REPO_ROOT/docroot
$REPO_ROOT/bin/drush si openedu --site-name="OpenEDU $TARGET_ENV" --db-url="mysql://openedu:openedu@127.0.0.1/$TARGET_ENV" --account-pass='imagex' -y

# flush cache for good measure
$REPO_ROOT/bin/drush cr

# Done
echo "=== The build is complete ==="
