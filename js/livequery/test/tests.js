(function ($) {
	$(function(){
		QUnit.reset = function () {
			$.livequery.queries = [];
			$.livequery.queue = [];
		};
		
		test("could stop livequery two times", function() {
			// add live query
			$('div').livequery(function (){} , function() {}); 
			// stop all queries
			$.livequery.stop();
			// add another live query
			$('div').livequery(function (){} , function() {});
			// should not fail
			$.livequery.stop();
			
			ok(true);
		});

		test("Should call fn2 on stop", function() {
			var q1called = false;
			var q2called = false;
			$('div').livequery(function (){}, function() { q1called = true; }); 
			$('div').livequery(function (){}, function() { q2called = true; });
			$.livequery.stop();
			
			ok(q1called, "stop called for first query");
			ok(q2called, "stop called for second query");
		});

		asyncTest("Should call fn2 on die", function() {
			var div = $('<div>', {
				id : "test"
			});			
			div.appendTo('body');
			var check = false;
			
			$('div').livequery(function (){}, function() { 
				console.debug(this); 
				check = (this.id == "test"); 
			});
			
			div.remove();
			
			setTimeout(function(){
				ok(check, "fn2 called when div died"); 
				start();
			}, 40);
		});

		test("Should call fn", function() {
			var called = false;
			$('div').livequery(function (){ called = true; });
			ok(called, "fn called for the query");
		});
	});
})(jQuery);