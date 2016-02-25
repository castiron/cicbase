## Utilities
Inspired by such libraries as Laravel "helpers" or even Underscore.js, there are now several utility classes in cicbase that serve simple, but useful, purposes. The classes are in [`CIC\Utility`](../Classes/Utility). Take a gander at what we’ve got. The methods are all static and well-commented. These are also (or should be) very well tested so use these utilities with confidence and build great code!

Here are some of my favorites:

#### `Arr::safe` and `Arr::safePath`

A lot of times you don’t care whether a variable is set or not, but you need the value if it’s there. To avoid getting any warnings, you better use `isset()`:

```
if (isset($arr[$maybeIndex]) && $arr[$maybeIndex] == ‘foo’) {
  $this->runAway();
}
```

But a nicer way to do this is just:

```
if (Arr::safe($arr, $maybeIndex) == ‘foo’) {
  $this->runAway();
}
```

I know it’s not going to send us to the moon, but it certainly cleans up yer codes.

This also works recursively:

```
$arr = ['I' => ['1' => ['a' => 'this chapter...']]];
$firstChapter = Arr::safe($arr, ['I','1','a']);
```

Or simply:

```
$firstChapter = Arr::safePath($arr, 'I.1.a');
```

If you've written a lot of PHP, you'll understand that this is way more convenient than using `isset`. 

#### `Arr::column`

Another handy method is `Arr::column`. It's a modified version of PHP's standad `array_column` function, but a little more flexible. Using it, you can take a collection of database rows and index them by UID or by a title column or something. This makes working with complex array structures a little simpler. It does more than this, so read the comments and figure it out.

#### `Arr::describe` and `Arr::describeKeys`

Comparing 2 arrays in PHP is 1) not hard and 2) needed often. These methods provide an effective way to get a quick rundown of how 2 arrays compare. 

#### `Str::pluralize`

Sometimes you need to render a string like `X coconuts` or `X coconut` and which version depends on whether `X` equals `1`. 

```
$x = 1
$str = $x . ' ' . Str::pluralize($x, 'coconut'); // "1 coconut"
$x = 11
$str = $x . ' ' . Str::pluralize($x, 'coconut'); // "11 coconuts"
$x = 111
$str = $x . ' ' . Str::pluralize($x, 'child'); // "111 children"
```

#### More!

There’re a ton of goodies in the utility classes and you should add more. 

[back to docs](.)