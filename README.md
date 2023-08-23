# Rename Module - Drupal Module 

Rename Module is a Drupal module that provides a Drush command to rename custom Drupal modules.
It handles the renaming of directories, files, and occurrences within files, ensuring a smooth
transition from the old module name to the new one.

## Features 

- **Locate Module**: Finds the specified module within the Drupal filesystem. 
- **Rename Directory**: Renames the directory containing the module. 
- **Rename Files**: Renames all files within the module directory that start with the old module name. 
- **Replace Occurrences**: Safely replaces all occurrences of the old module name within the files. 

## Installation 

1. Download the module to your custom modules directory. 
2. Enable the module using Drush: \`drush en [module_name]\`. 
3. Clear the cache: `drush cr`. 

## Usage 

Use the Drush command to rename a module: 

```bash 
drush rename-module:rename [old_name] [new_name] 
``` 

## Requirements 

- Drupal 9 or 10 
- Drush 

## Contributing 

Contributions are welcome!
