# Edge Side Includes (ESI)

https://en.wikipedia.org/wiki/Edge_Side_Includes

## Example with ConLayout

````xml
<?xml version="1.0" encoding="UTF-8"?>
<layout>
    <reference>
        <my.widget>
            <esi ttl="120">
                <handles handle="application/index/index" />
            </esi>
        </my.widget>
    </reference>
</layout>
````

Note: The `handles` option is only necessary if the block was not defined within the `default` handle.

