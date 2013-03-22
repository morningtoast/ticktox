<!DOCTYPE html>
<html>
<head>
	<title>Ticktox</title>
	<style>
		body { background-color:#333; }
		#stage .block { width:20%; float:left; }
		#stage .block .action { width:100%; height:80px; color:#ccc; }
		#stage .block .clear { display:none; }
		#stage.user .block.empty .action { background-color:#333; }
		#stage.user .block.active.on .action { background-color:#0f0; color:#333; }
		#stage.user .block.active.off .action { background-color:#f00; color:#333; }
		#stage.admin .block .action { background-color:#ccc; color:#333; }
		#stage.admin .block.active .action span:before { content:"X"; padding-right:3px; }

		#log { color:#0f0; clear:both; }
	</style>
	<script type="text/javascript" src="./stack.js"></script>
</head>
<body>
<div id="controls">
	<button id="editall">Edit Tasks</button>
	<button id="report">Report</button>
</div>
<div id="stage" class="user"></div>
<pre id="log">

</pre>


<script id="tmpl-block" type="text/x-jquery-tmpl">
	<div class="block {{defaultClass}}" data-state="{{state}}" data-id="{{id}}">
		<button class="action" data-name="{{name}}"><span>{{name}}</span></button>
	</div>
</script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
<script>
var App = (function($, mz) {
	var vars = {
		"mode":"user",
		"id":"12345"
	}
	
	
	var db = {
		"data":[],
		"init":function(callback) {
			var self = this;
			$.post("db.php",{"init":vars.id}, function(response) {
				console.log(response);
				self.data = JSON.parse(response);
				callback();
			});
		},
		"save":function() {

			var store = []; // Clears current DB

			$("#stage .block").each(function(k,v) {
				var item = $(this);
				var dataSet = {
					"id":item.data("id"),
					"name":item.find("button").data("name")
				}
				store.push(dataSet);
			});
			
			$.post("db.php",{"save":vars.id,"data":store}, function(response) {
				console.log(response);
			});			
		}
	
	}	

	// Private
	var local = {
		"init":function() {
			db.init(function() {
				local.renderBlocks(25);
				bind.init();
			});
		},

		"renderBlocks":function(limit) {
			var tmpl = $("#tmpl-block").html();		
			$.each(db.data.index, function(k,viewData) {
				if (viewData.name != "empty") { viewData.defaultClass = "active off"; } else { viewData.defaultClass="empty"; }

				var render = Mustache.render(tmpl, viewData);

				$("#stage").append(render);
			});
		},

		"handleBlockAction":function() {
			if (vars.mode == "user") {
				local.handleUserAction(this);
			} else {
				local.emptyBlock(this);
			}
		},

		"handleUserAction":function(el) {
			var clicked     = $(el);
			var clickParent = clicked.parent();
			var taskName    = clicked.data("name");

			if (taskName == "empty") {
				var taskName = prompt("What should this task be called?");
				
				if (taskName) {
					clicked.data("name",taskName);
					clicked.find("span").html(taskName);
					clickParent.removeClass("empty").addClass("off").addClass("active");

					db.save();
				}
			} else {
				if (clickParent.hasClass("on")) {
					// Turn off clicked task
					local.logLastTask();
					clickParent.removeClass("on").addClass("off"); 
				} else {
					// Turn on clicked task, turn off others
					local.logLastTask();
					local.logTask(taskName);
					$(".block.active").removeClass("on").addClass("off");
					clickParent.removeClass("off").addClass("on");
				}
			}			
		},

		"logLastTask":function() {
			var taskName = $("#stage .on .action").data("name");

			if (taskName != undefined) {
				$.post("db.php",{"log":vars.id,"task":taskName,"state":"off"},function(response) {
					$("#log").append("OFF: "+taskName+"\n");
				});
				
			}
		},

		"logTask":function(taskName) {
			$.post("db.php",{"log":vars.id,"task":taskName,"state":"on"},function(response) {
				$("#log").append("ON: "+taskName+"\n");
			});
			//$("#log").append("ON: "+taskName+"\n");
		},

		"adminSwitch":function() {
			//$("#stage").removeClass("user").addClass("admin");
			if (vars.mode == "admin") {
				vars.mode = "user";
				$("#stage").removeClass("admin").addClass("user");
			} else {
				vars.mode = "admin";
				$("#stage").removeClass("user").addClass("admin");
			}
		},

		"emptyBlock":function(el) {
			var clicked     = $(el);
			var clickParent = clicked.parent();

			clicked.data("name","empty");
			clicked.find("span").html("empty");
			clickParent.addClass("empty").removeClass("active");

			db.save();
		},
		
		"showReport":function() {
			$.post("db.php",{"report":vars.id},function(response) {
				$("#log").html(response);
			});
		}
	}
	


	// Binds
	var bind = {
		"init":function() {
			bind.userActions();
			bind.adminSwitch();
			bind.report();
		},
	
		"report":function() {
			$("#report").on("click", local.showReport);
		},
		
		"userActions":function() {
			$("#stage").on("click", ".block .action", local.handleBlockAction);
		},

		"adminSwitch":function() {

			$("#editall").on("click",local.adminSwitch);
		}
	}


	// Public
	var global = {
		"init":function() { local.init(); },
	}







    return(global);
}(jQuery, Modernizr));

App.init();
</script>

</body>
</html>