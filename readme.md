# Bob - An API Query String Builder

This is a Laravel package that will allow you to create query scopes that can be applied to an API's Endpoint dynamically via Query Strings.

## Huh?

In short, you can apply filters to your API endpoints like so

```
/posts?with=comments&author=2$sort=created_at,asc
```

This would return all posts for the author of ID 2, all comment relationships and sorted by created_at date (note: some assembly required)

## Why?

It should be no surprise but Spatie has a great package https://github.com/spatie/laravel-query-builder that pretty much does this out of the box. If your use case is simple enough this will likely be enough and would be my recommendation.

So why create this? well two reasons

1. I wanted to learn the process of creating a composer package and figured I would extract a simple class that I have been using on a project of mine.
2. My use case was a bit more complicated as I also wanted to support searching via Scout and well that wasn't possible so hence my little class (and now package)

## Ok sounds good, let's install and setup 

### Install via composer

```
composer install jeffbeltran/bob
```

If you want the built in “search” feature to work you will need to have scout configured and setup, the scope of this readme does not cover those details, please refer to [The Laravel Scout Docs](https://laravel.com/docs/5.6/scout) for more information on how to configure it. 

### Create a “Blueprint“

So in order for Bob to do his job you’ll need to create some blueprints he can follow. These are pretty simple and require you to understand how [Laravel’s Query Builder](https://laravel.com/docs/5.6/queries) works.  Some blueprint names are “reserved” but other than the three below, you will need to create a filter (blueprint) you want to apply to an endpoint will need to manually be created. 

#### Default/Reserved Blueprints

Due to a few issues I have reserved `with`, `limit` and `search`. So in other words these blueprints will work out of the box. All others will need to be created as below.  See [Builtin Blueprints for more info](#builtin-blueprints)

#### Blueprint Requirements

You will need to group all the blueprints under the same namespace for a specific endpoint. So if we keep using the above example (`/posts?with=comments&author=2$sort=created_at,asc`), you would have two classes in the `App\Blueprints\Posts` namespace;  `Author` and `Sort`.

All of these classes require an `apply` method that takes the builder instance and the value of the query string present.

#### Blueprint Examples

Since I like examples, here are the two classes you would have to create for 
 `/posts?with=comments&author=2$sort=created_at,asc`

```php
<?php

namespace App\Blueprints\Posts;

use JeffBeltran\Bob\Blueprint;

class Sort implements Blueprint
{
    public static function apply($builder, $value)
    {
        $sortBy = explode(',', $value);
        return $builder->orderBy($sortBy[0], $sortBy[1]);
    }
}
```

```php
<?php

namespace App\Blueprints\Posts;

use JeffBeltran\Bob\Blueprint;

class Author implements Blueprint
{
    public static function apply($builder, $value)
    {
        return $builder->where('author_id', $value);
    }
}
```

_Note that I didn’t include a With class, this is because it is already built into the default behavior._ 

### Builtin Blueprints

`Limit`, `With` and `Search` are all reserved due to the way you need to process the filters/blueprints

#### Limit
Mockup: `limit=paginationlength`
Example: `/posts?limit=5` 
Description: This is how you would paginate the returned data into Laravel’s Length Aware Paginator
#### With
Mockup: `with=relation_one_name,relation_two_name`
Example: `/posts?with=comments`
Description: This is how you would get  the resources with their relationships. You can load multiple relationships
by using commas to separate different relationship names.
#### Search
Mockup: `search=somesearchterm`
Example: `/posts?search=bobthebuilder` 
Description: This allows you to search the endpoint with your scout instance.

### Caveats and other tidbits

* Scout Doesn’t support the full range of Laravel’s Query Builder, so filters applied to searches will likely run into issues.
* Bob will always return a collection of results unless you call the limit blueprint which will then return a Length Aware Paginator

## Ok now what?

Assuming you are following a “RESTful” design. Then you would want your controller Index and Show methods to look like this. 

```php

	use JeffBeltran\Bob\TheBuilder;

	class PostController extends Controller
	{
	    public function index()
	    {
	        $bob = new TheBuilder(Post::class);
	        return $bob->getResults();
	    }
	
	    public function show($id)
	    {
	        $queryFilter = new TheBuilder(Post::class, $id);
	        return $bob->getResults();
        }
        
        ...
        
	}
```


## Hey can I help?

Yeah that would be great, just send over a pull request and we’ll go from there.

## Versioning

I’ll be using [SemVer](http://semver.org/) for versioning. For the versions available, see the [tags on this repository](https://github.com/JeffBeltran/bob/tags). 

## Authors

* Jeff Beltran - **Initial work** - https://github.com/JeffBeltran

See also the list of [contributors](https://github.com/JeffBeltran/bob/contributors) who participated in this project.

## License

This project is licensed under the MIT License