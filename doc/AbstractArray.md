## `AbstractArray`

PHP allows you to implement various interfaces to create structures that will work fluidly with the language. For example, to make a structure that can be looped through using `foreach`, you have to implement the `\Iterable` interface. This is great, but sometimes your custom structure isn't that much different than a regular array. By extending the `AbstractArray` class, you're already set with some pre-implemented methods that defer to an internal array.

For example, let's say you've created a cursor structure that pulls items from a paginated API. On the first iteration, you pull in the first page of results and then subsequent iterations will draw from those results. When that first page runs out, you'd make another request to the API to get the second page of results and then iterate over those. 

That's rather simple and you're really only maintaing the array of results. That is, the structure itself is not that complicated. It's just the lifecycle of iteration that you're concerned with. So, instead of writing the `current()`, `key()`, `next()` methods which you just pass on to the array anyway, you only need to concern yourself with writing the `rewind()` method and probably the `count()` method. 

Basically, use this when your internal structure is just an array and you don't feel like writing all the dumb wrappers that just defer to that internal array.
