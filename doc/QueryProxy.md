## `QueryProxy` and `AbstractRepository`

ExtBase actually provides a fairly complex and comprehensive query object model to get your domain records. Unfortunately, it's very verbose and ugly (like a lot of PHP). 

For example, let's say you need to get all objects that are green and square. In your repository function, you'd do something like this:

```
$query = $this->createQuery();

$constraints = [];
$constraints[] = $query->equals('color', 'green');
$constraints[] = $query->equals('shape', 'square');

$and = $query->logicalAnd($constraints);

$result = $query->matching($and)->execute();
```

It's clear what's going on, but it's also dumb.

#### Using `QueryProxy`

Instead, let's use the `AbstractRepository` class that uses the `QueryProxy` under the hood. Now, you can do that same query like this:

```
$results = $this->query(function($q) {
  $q->equals('color', 'green');
  $q->equals('shape', 'square');
});
```

That's it. Still very clear what's going on, but _way_ less verbose. 

#### ORing clauses by default

If you needed to get objects that were green OR square, that's also easy:

```
$results = $this->query(function($q) {
  $q->equals('color', 'green');
  $q->equals('shape', 'square');
}, self::LOGICAL_OR);
```

#### Nested query clauses

You can even get more complex:

```
$results = $this->query(function($q) {
  $q->logicalOr(function($q2) {
    $q2->equals('color', 'green');
    $q2->equals('shape', 'square');
  });
  $q->equals('enabled', true);
});
```

That ends up with a clause looking like:

```
(color = 'green' OR shape = 'square') AND enabled = 1
```

#### Extra functionality

The `QueryProxy` has all the normal query constraint methods (i.e. `greaterThan`, `greaterThanOrEqual`, `in`, `setOffset`, etc.). There are few more too:

```
$resultsFromAnyPage = $this->query(function($q) {
  $q->setRespectStoragePage(false);
  # and/or $q->setIncludeDeleted(true);
  # and/or $q->setIgnoreEnableFields(true);
  $q->equals('color', 'green');
  $q->equals('shape', 'square');
});
```


#### Raw results

Let's face it. ExtBase's property mapping is great, but can be quite cumbersome. It's faster to skip the mapping and work with the raw database rows. 

```
$results = $this->rows(function($q) {
  $q->equals('color', 'green');
  $q->equals('shape', 'square');
});

# or just get the IDs

$ids = $this->rows(function($q) {
  $q->equals('color', 'green');
  $q->equals('shape', 'square');
}, self::LOGICAL_AND, 'uid');
```

#### Count

```
$count = $this->count(function($q) {
  $q->equals('color', 'green');
  $q->equals('shape', 'square');
});
```

