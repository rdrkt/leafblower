profiles:[
	{
		_id: "my-awesome-profile",
		name: "My Awesome Profile",
		description: "",
		
		blocks: {
			"countingMongodb" : { options:{value1: joshua}, ttl: 100 }, 
			"counting" : { },
		}
		
	},
	
	
]
users:
[
	{
		"_id" : "rdrkt",
		"password" : "e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855",
		profiles: [  ObjectId("4f3ed089fb60ab534684b7e9"),  ObjectId("8d3eeeefb60ab534684b7e9") ],
	},
	{
		"_id" : "andy",
		"password" : "e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855",
		profiles: [  ObjectId("4f3ed089fb60ab534684b7e9"),  ObjectId("8d3eeeefb60ab534684b7e9") ],	
	}
]



blocks:[
	{
		_id: "countingMongodb",
		type: "counting",
		title: "Mongodb Counting",
		icon: "icon.jpg",
		description: "Block for visualizing the size of collections and their indexes",
		ttl: 5000,
		options: { 
			value1: default,
			value2: default2,
		}
	},
	{	_id: "countingBeanstalkd",
		type: "counting",
	

	},
]







histories:

