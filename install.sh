#!/bin/bash

set -e

SKIP_TESTS=false

# Parse flags
while [[ "$#" -gt 0 ]]; do
    case $1 in
        --skip-tests) SKIP_TESTS=true ;;
        *) echo "Unknown parameter passed: $1"; exit 1 ;;
    esac
    shift
done

echo "Starting project installation..."

echo "Running 'composer install'..."
composer install

echo "Copying '.env.example' to '.env'..."
cp .env.example .env

echo "Starting Sail containers..."
./vendor/bin/sail up -d

echo "Generating JWT secret..."
./vendor/bin/sail artisan jwt:secret --force

if [ "$SKIP_TESTS" = false ]; then
    echo "Running tests..."
    ./vendor/bin/sail artisan test
else
    echo "Skipping tests (used the '--skip-tests' flag)"
fi

echo "Project installation completed successfully!"

cat << "EOF"


/============================================================================================\
|| _______                  _                                                  _____ _____  ||
|||__   __|                | |     /\                                    /\   |  __ \_   _| ||
||   | |_ __ __ ___   _____| |    /  \   __ _  ___ _ __   ___ _   _     /  \  | |__) || |   ||
||   | | '__/ _` \ \ / / _ \ |   / /\ \ / _` |/ _ \ '_ \ / __| | | |   / /\ \ |  ___/ | |   ||
||   | | | | (_| |\ V /  __/ |  / ____ \ (_| |  __/ | | | (__| |_| |  / ____ \| |    _| |_  ||
||   |_|_|  \__,_| \_/ \___|_| /_/    \_\__, |\___|_| |_|\___|\__, | /_/    \_\_|   |_____| ||
||                                       __/ |                 __/ |                        ||
||                                      |___/                 |___/                         ||
\============================================================================================/
"
EOF