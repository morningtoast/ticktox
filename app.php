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
		"mode":"user"
	}

	// Private
	var local = {
		"init":function() {
			if (mz.localstorage) {
				local.iniLocalStorage();
			}

//localStorage.removeItem("index");
console.log(localStorage.getItem("index"));
			local.renderBlocks(25); bind.init();
		},

		"renderBlocks":function(limit) {
			var tmpl = $("#tmpl-block").html();
			var db   = localStorage.getItem("index");

			// New load
			if (db == null) {
				db = [];
				for (a=1; a <= limit; a+=1) {
					db.push({"id":a,"name":"empty","defaultClass":"empty","state":"empty"});
				}

				db = JSON.stringify(db);
			}

			db = JSON.parse(db);
	
			$.each(db, function(k,viewData) {
				if (viewData.name != "empty") { viewData.defaultClass = "active off"; } else { viewData.defaultClass="empty"; }

				var render = Mustache.render(tmpl, viewData);

				$("#stage").append(render);
			});

			local.saveDb();
		},

		"iniLocalStorage":function() {
			localStorage.setItem("db","[]");
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

					local.saveDb();
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
				$("#log").append("OFF: "+taskName+"\n");
			}
		},

		"logTask":function(taskName) {
			$("#log").append("ON: "+taskName+"\n");
		},

		"saveDb":function() {

			var store = []; // Clears current DB

			$("#stage .block").each(function(k,v) {
				var item = $(this);
				var dataSet = {
					"id":item.data("id"),
					"name":item.find("button").data("name"),
					"state":item.data("state")
				}
				store.push(dataSet);
			});




			//localStorage.db = JSON.stringify(store);
			localStorage.removeItem("index");
			localStorage.setItem("index",JSON.stringify(store));

			console.log("saving...");
			console.log(localStorage.getItem("index"));

//console.log("just saved ="+localStorage.getItem("db"));

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

			local.saveDb();
		}
	}

	// Binds
	var bind = {
		"init":function() {
			bind.userActions();
			bind.adminSwitch();
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