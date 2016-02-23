## SQL Logger

This is handy tool for debugging complicated SQL queries. You'd only use this while developing.

TYPO3 provides the ability to get the last executed query, but by the time your breakpoint is reached, other queries may have been executed and the one you're interested is no longer there. Lucky for us, TYPO3 also sends out a signal about the last executed query and we can save that info for use by us later. The [SQLLogger](../Classes/Persistence/SQLLogger.php) keeps tabs of the last executed queries _by table_. So now you can at least see the last executed query for your table. It's not a perfect solution, but it does the trick about 95% of the time. And if you've ever debugged your way through ExtBase to figure out what the query was, you'll see that this can be super handy.

To enable, add this to your `environment/localconf_context.php` file:

```
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['cicbase']['enableSQLLogging'] = 1;
```

And then at whatever you point you expect the query has been executed, you can put this in your code to see what the query was:

```
$q = SQLLogger::getLastQuery('tx_myext_domain_model_mymodel');
```

Woohoo!

Now, things to note:
* We only save the last query by table, so if another query to your table was made that you aren't interested in, this may not work for you.
* ExtBase `QueryResult` objects do not execute until the last minute, which is basically when you're actually getting the objects. So you may need to inspect the query while in a `foreach` loop or call `getFirst` or `toArray` on the `QueryResult` object first.

[back to docs](.)