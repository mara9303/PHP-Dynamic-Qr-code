#!/bin/bash
set -e

# Read the SQL file and replace #prefix# with DATABASE_PREFIX value (empty by default)
SQL_FILE="/docker-entrypoint-initdb.d/sample_data.sql.template"
PREFIX="${DATABASE_PREFIX:-}"

echo "Initializing database with prefix: '${PREFIX}'"

# Replace #prefix# with the actual prefix value and execute
sed "s/#prefix#/${PREFIX}/g" "$SQL_FILE" | mysql -u root -p"${MYSQL_ROOT_PASSWORD}" "${MYSQL_DATABASE}"

echo "Database initialized successfully!"
