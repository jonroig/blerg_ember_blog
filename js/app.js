
// create the app as App
App = Ember.Application.create();


// setup the datasource REST adaptor
App.ApplicationAdapter = DS.RESTAdapter.extend({
  namespace: 'ember_backend',
  host: 'http://127.0.0.1'
});


// create some models
App.Story = DS.Model.extend({
    title: DS.attr('string'),
    creationDatetime: DS.attr('string'),
    body: DS.attr('string'),
   	topicId: DS.attr('string')
});

App.Topic = DS.Model.extend({
	counter: DS.attr(),
    topicText: DS.attr('string'),

    counter: function()
	{
		return 0;
	}.property()
});


// we need some stub controllers to hold stuff later
App.TopicsController = Ember.ArrayController.extend({
	content:[]
});
App.StoriesController = Ember.ArrayController.extend({
	content:[]
});
App.NewstoryController = Ember.ObjectController.extend({
	content:[]
});

// now here are the more complicated controller
App.StoryController = Ember.ObjectController.extend({
	isEditing: false,

	actions:{
		edit: function() {
			this.set('isEditing', true);
		},
		doneEditing: function() {
	    	this.set('isEditing', false);
	    	this.get('model').save();
	  	}
	}
});



// now we're gonna have some routing
App.Router.map(function () {
	this.resource('index', { path: '/' });
	this.resource('topics');
	this.resource('topic', { path: 'topic/:topic_id' });
  	this.resource('story', { path: 'story/:story_id' });
  	this.resource('newstory');
});


// here are the actual routes
App.IndexRoute = Ember.Route.extend({
	model: function() {
    	var store = this.get('store');
    	return store.find('story', {latest: true});
  	},

	setupController: function(controller, model) {
		var content =  this.get('store').find('topic'),
   	
   		controller2 = this.controllerFor('Topics');
   		controller2.set('content', content);

   		controller.set('content',model);
 
     	this.render('topics', {controller: controller2, outlet: 'topics'});
     	return content;
 	}
});

App.TopicsRoute = Ember.Route.extend({
	model: function() {
    	var store = this.get('store');
    	return store.find('topic');
  	}
});

App.TopicRoute = Ember.Route.extend({
	setupController: function(controller, model) {
		controller.set('content',model);
		var store = this.get('store');

		var content =  store.find('topic'),
   	
   		controller2 = this.controllerFor('Topics');
   		controller2.set('content', content);

   		this.render('topics', {controller: controller2, outlet: 'topics'});

   		var storyContent = store.find('story', {getByTopicId: model.id});
     	storyController = this.controllerFor('Stories');
     	storyController.set('content', storyContent);
     	this.render('shortStories', {controller: storyController, outlet: 'stories'});

     	return content;
	}
});

App.StoryRoute = Ember.Route.extend({
	setupController: function(controller, model) {
		var store = this.get('store');
   		controller.set('content',model);

   		// handle topics controller
		var content2 =  store.find('topic'),
   		controller2 = this.controllerFor('Topics');
   		controller2.set('content', content2);
     	this.render('topics', {controller: controller2, outlet: 'topics'});

     	return content2;
 	}
});

App.NewstoryRoute = Ember.Route.extend({

  	setupController: function(controller, model) 
  	{
  		var store = this.get('store');
  		model = store.createRecord('story');
   		controller.set('content',model);

   		console.log(controller);

   		var content2 =  store.find('topic'),
   		controller2 = this.controllerFor('Topics');
   		
   		App.availableTopics.set('content', content2);
   		controller2.set('content', content2);
     	this.render('topics', {controller: controller2, outlet: 'topics'});

     	controller.set('topicsList', content2);
  	}
});

App.availableTopics = Ember.Object.create({
  selected: null,
  content: [],
  	counter: function()
	{
		return 0;
	}.property()
});


App.NewstoryController = Ember.ObjectController.extend({

	actions:{
		save: function() {
            this.get('model').save();
        }
	},

});

