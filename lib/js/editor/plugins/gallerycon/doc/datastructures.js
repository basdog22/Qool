/**
 * A description of the datastructures used by the plugin.
**/

// Settings for the plugin
{
	"urls":
	{
		"galleries": "http://127.0.0.1:8000/myndir/galleries?format=json&jsoncallback=?",
		"images": "http://127.0.0.1:8000/myndir/images/{gallery_id}?format=json&jsoncallback=?",
		"image": "http://127.0.0.1:8000/myndir/image/{image_id}?format=json&jsoncallback=?",
		"img_src": "http://127.0.0.1:8000/myndir/image_src/{image_id}/{size_id}?format=json&jsoncallback=?"
	},
	"sizes":
	[
		{
			"id": "thumbnail",
			"name": "Thumbnail"
		},
		{
			"id":  "litebox",
			"name": "Display size"
		},
		{
			"id": "small",
			"name": "Small"
		},
		{
			"id": "medium",
			"name": "Medium"
		}
	]
}

// Sample data from calling the "galleries" URL:
[
	{
		"id": 1,
		"title": "My animal gallery",
		"desc": "This is a collection of my animal pictures"
	},
	{
		"id": 2,
		"title": "My human gallery",
		"desc": "This is a collection of pictures of humans"
	}
]

// Sample data from calling the "images" URL:
[
	{
		"id": 1,
		"title": "My cat",
		"desc": "This is a picture of my cat",
		"thumb": "/photologue/photos/utskrift/cache/cat.jpg",
	},
	{
		"id": 2,
		"title": "My dog",
		"desc": "Isn't he cute",
		"thumb": "/photologue/photos/utskrift/cache/dog.jpg"
	}
]

// Sample data from calling the "image" URL:
{
	"id": 1,
	"title": "My cat",
	"desc": "This is a picture of my cat",
	"thumb": "/photologue/photos/utskrift/cache/cat.jpg",
	"data": {
		"photographer": "Bjarni Thorisson",
		...// Here you could put the EXIF data f.ex.
	}
}

// Sample data from calling the "img_src" URL:
{
	"image_id": 123,
	"size": "medium",
	"src": "http://mysite.com/photos/bla/myimage_medium.jpg"
}
