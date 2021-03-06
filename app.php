<!DOCTYPE html>
<html>
<head>
	<title>Ticktox</title>
	<style>
		body { background-color:#000; padding:0; margin:0; font-family:arial,helvetica,sans-serif; color:#ccc; }
		button { font-family:roboto,verdana,tahoma,arial,helvetica,sans-serif; text-transform:uppercase;font-style:italic;}
		#stage { border-top:solid 1px #333; border-left:solid 1px #333;overflow:auto; }
		#stage .block { width:20%; float:left; }
		#stage .block .action { cursor:pointer; font-size:14px; width:100%; height:80px; color:#ccc; border-left:none; border-top:none; border-right:solid 1px #333;border-bottom:solid 1px #333;}
		#stage .block .clear { display:none; }
		#stage.user .block.empty .action { background-color:#1a1a1a; color:#666; }
		#stage.user .block.active.on .action { background-color:#5DA700; color:#fff; }
		#stage.user .block.active.off .action { background-color:#810000; color:#ccc; }
		
		#stage.admin .block button { background:none; }
		#stage.admin .block.empty { background-color:#1a1a1a; color:#333; }
		#stage.admin .block.empty button { background-color:#1a1a1a; color:#333; }
		
		#stage.admin .block.active .action { background-color:#999; color:#000; }
		x#stage.admin .block.active .action span:before { content:"X"; padding-right:3px; }

	#controls { text-align:right; color:#333;}
		#controls button { cursor:pointer; background:none; border:none; color:#333; font-size:13px; }

		#stats { color:#A0A70C; font-size:11px; font-family:verdana,arial,helvetica,sans-serif; }
		
		#log { color:#A0A70C; clear:both; height:200px; overflow:hidden; }
		#chart { margin:10px 0; overflow:hidden;width:100%;}
		#chart .bar { position:relative;font-size:11px;color:#ccc;float:left;height:40px; border-right:solid 1px #000; overflow:hidden; }
		#chart .bar span { position:absolute; bottom:2px; left:4px; width:500px; }
		#chart .bar.p1 { background-color:#3366FF; }
		#chart .bar.p10 { background-color:#5C66F5; }
		#chart .bar.p25 { background-color:#8566EB; }
		#chart .bar.p50 { background-color:#AD66E0; }
		#chart .bar.p75 { background-color:#D666D6; }
		#chart .bar.p90 { background-color:#FF66CC; }
		
		#stats { width:100%; overflow:auto; padding:0 20px;}
		#stats .column { float:left; width:33%; }
	</style>
	<script type="text/javascript" src="./stack.js"></script>
	<link href='http://fonts.googleapis.com/css?family=Roboto:300,400|Roboto+Condensed:300italic,400' rel='stylesheet' type='text/css'>
</head>
<body data-id="12345">
<div id="controls"></div>
<div id="stage" class="user"></div>
<div id="chart"></div>
<div id="stats">
	<div class="column">
		<strong>LOG</strong>
		<div id="log"></div>
	</div>
	<div class="column">
		<strong>SUMMARY</strong>
		<dl id="summary"></dl>
	</div>
	<div class="column">
		<strong>SETTINGS</strong>
		<dl>
			<dt><button id="editall">Remove Tasks</button></dt>
			<dt><button id="report">Refresh Report</button></dt>
			<dt><button id="reset">Reset</button></dt>
		</dl>
	</div>
</div>
<div id="log"></div>
<audio preload autoplay></audio>


<script id="tmpl-block" type="text/x-jquery-tmpl">
	<div class="block {{defaultClass}}" data-state="{{state}}" data-id="{{id}}">
		<button class="action" data-name="{{name}}"><span>{{name}}</span></button>
	</div>
</script>
<script id="tmpl-log" type="text/x-jquery-tmpl">
	<pre class="row">[{{hours}}] {{task}}: {{state}}</pre>
</script>
<script id="tmpl-barchart" type="text/x-jquery-tmpl">
	{{#data}}<div class="bar" data-percent="{{percent}}" style="width:{{percent}}%;background-color:{{barcolor}}"><span>{{task}}</span></div>{{/data}}
</script>
<script id="tmpl-summary" type="text/x-jquery-tmpl">
	{{#data}}<dt><span>{{task}}: {{hours}}hrs</span></dt>{{/data}}
</script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
<script>
var App = (function($, mz) {
	var vars = {
		"mode":"user",
		"id":$("body").data("id"),
		"title":" | Ticktox"
	}
	
	
	var db = {
		"index":[],
		"init":function(callback) {
			var self = this;
			$.post("./db.php",{"init":"1","id":vars.id}, function(response) {
				self.index = JSON.parse(response);
				callback();
			});
		},
		"save":function() {
			var store = []; // Clears current DB

			$("#stage .block").each(function(k,v) {
				var item    = $(this);
				var dataSet = {
					"id":item.data("id"),
					"name":item.find("button").data("name")
				}
				store.push(dataSet);
			});
			
			$.post("db.php",{"save":"1","id":vars.id,"data":store});			
		}
	
	}	

	// Private
	var local = {
		"init":function() {
			db.init(function() {
				local.renderBlocks(25);
				bind.init();
				local.showReport();
				local.startTimer(600); // In seconds
			});
		},

		"renderBlocks":function(limit) {
			var tmpl = $("#tmpl-block").html();		
			$.each(db.index, function(k,viewData) {
				//if (viewData.name != "empty") { viewData.defaultClass = "active off"; } else { viewData.defaultClass="empty"; }

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
			var blockId     = clicked.data("id");

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
					// Turn off clicked task, not turning on another
					local.logLastTask();
					clickParent.removeClass("on").addClass("off"); 
					$("title").html("NOT RUNNING"+title);
					local.stopTimer();
				} else {
					// Turn on clicked task, turn off others
					local.logLastTask();
					local.logTask(taskName);
					$(".block.active").removeClass("on").addClass("off");
					clickParent.removeClass("off").addClass("on");
					local.playAudio();
					local.stopTimer();
					local.startTimer(600); // In seconds

					$("title").html(taskName+vars.title);
				}
			}			
		},

		"logLastTask":function() {
			var taskName = $("#stage .on .action").data("name");

			if (taskName != undefined) {
				$.post("db.php",{"log":"1","id":vars.id,"entry":{"task":taskName,"state":"off"}},function(response) {
					local.displayLog(JSON.parse(response));
				});
			}
		},

		"logTask":function(taskName) {
			$.post("db.php",{"log":"1","id":vars.id,"entry":{"task":taskName,"state":"on"}},function(response) {
				local.displayLog(JSON.parse(response));
			});
		},

		"displayLog":function(data) {
			var tmpl = $("#tmpl-log").html();
			var render = Mustache.render(tmpl, data);
			$("#log").append(render);
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
			$.post("db.php",{"report":vars.id,"id":vars.id},function(response) {
				var data  = JSON.parse(response);
				var chart = {"data":data};
				
				var tmpl   = $("#tmpl-barchart").html();
				var render = Mustache.render(tmpl, chart);
				$("#chart").html(render);	

				$("#chart .bar").each(function() {
					var p = $(this).data("percent");
					var c = "p1";
					if (p > 5) { c = "p10"; }
					if (p > 10) { c = "p25"; }
					if (p > 15) { c = "p50"; }
					if (p > 20) { c = "p75"; }
					if (p > 25) { c = "p90"; }
					
					$(this).addClass(c);
				});
				
				var tmpl   = $("#tmpl-summary").html();
				var render = Mustache.render(tmpl, chart);
				$("#summary").html(render);
			});
		},

		"playAudio":function() {
			$("audio").attr("src","beep.mp3");
		},

		"startTimer":function(sec) {
			local.timer = window.setInterval(local.playAudio,(sec * 1000));
		},

		"stopTimer":function() {	
			window.clearInterval(local.timer);
		},

		"resetAll":function() {
			$.post("db.php",{"reset":vars.id, "id":vars.id},function(response) {
				window.location = "./app.php";
			});
		}
	}
	


	// Binds
	var bind = {
		"init":function() {
			bind.userActions();
			bind.adminSwitch();
			bind.report();
			bind.reset();
		},
	
		"report":function() {
			$("#report").on("click", local.showReport);
		},
		
		"userActions":function() {
			$("#stage").on("click", ".block .action", local.handleBlockAction);
		},

		"adminSwitch":function() {

			$("#editall").on("click",local.adminSwitch);
		},

		"reset":function() {
			$("#reset").on("click",local.resetAll);
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