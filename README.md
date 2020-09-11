# Query Builder Filters

## Installation
Simply add the following line to your `composer.json` and run install/update:

    $ composer require osi-open-source/lumen-query-builder-filters

## Usage
With Filters
    
    /users?name=John&with=roles&take=3&skip=15

With JSON Filters

    /users?filters={"name":{"value":"John","pattern":"LIKE","not":false}}&skip=2&take=15
