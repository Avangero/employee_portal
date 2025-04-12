#!/bin/bash

echo "Запуск pre-commit проверок..."

# Get list of staged files
STAGED_FILES=$(git diff --staged --name-only --diff-filter=ACM)

# Exit if no files are staged
if [ -z "$STAGED_FILES" ]; then
    echo "Нет файлов для коммита"
    exit 0
fi

# Run Duster on PHP files
echo "Запуск Duster для PHP файлов..."
./vendor/bin/duster fix

# Run Prettier on staged files
echo "Запуск Prettier для форматирования кода..."
npx prettier --write $STAGED_FILES

# Add fixed files back to staging
echo "Добавление отформатированных файлов обратно в staging..."
git add $STAGED_FILES

echo "Pre-commit проверки успешно завершены!"
exit 0 