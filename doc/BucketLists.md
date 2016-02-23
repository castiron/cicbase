## Bucket Lists

If this isn’t something you should use before you die, then I have no idea what it is.

Every so often, you need to list things according to a particular order that doesn’t make sense by just looking at it. Let’s say you need to render a list of photos with categories ordered by IDs `7, 3, 2, 5`. So photos in category `7` are listed first, then photos in category `3`, etc. How would you do this?

Well you’d probably create buckets. Then you’d add sorted photos to each bucket and loop through the buckets in the right order to make one big list. Not too hard to fathom.

Now let’s say you need to list news articles in the same category order, and events, and people, etc. Are you really going to do the same algorithm over and over? Of course not.

This is what you’d do:

```
$list = new BucketList([7,3,2,5]);
foreach ($unsortedPhotos as $photo) {
  $list->insert($photo, $photo->categoryID);
}
…
foreach ($list as $photo) {
  $currentCategoryID = $list->currentBucket();
  $this->renderMySortedPhoto($photo);
}
```

Whaaaauutt? Mind. Blown.

You can even store info about the buckets:

```
foreach ($sortedCategories as $category) {
  $order[$category->id] = $category;
}
$list = new BucketList($order, TRUE);
foreach ($unsortedPhotos as $photo) {
  $list->insert($photo, $photo->categoryID);
}
…
foreach ($list as $photo) {
  $currentCategoryID = $list->currentBucket();
  $currentCategory = $list->currentBucketInfo();
  $this-> renderMySortedPhoto($photo);
}
```

So amaze.

[back to docs](.)