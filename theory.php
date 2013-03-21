<html>
<head>
	<style>
		.bar { background-color:#ccc; height:40px; width:200px; }
		.block { width:20%; float:left;  }
		#stage button { width:100%;  height:75px; border-radius:6px;  }
		.active { background-color:#f00; }
		.empty { background-color:#ccc; }
		.on { background-color:#0f0; }
		.setting { display:none; }
		.setting span { display:block; }
	</style>
</head>
<body>

<button class="action">Manage</button>
<hr />
<div id="stage"></div>
<div style="clear:both"></div>

<pre id="log">

</pre>
<audio preload autoplay></audio>

<script id="tmpl-block" type="text/x-jquery-tmpl">
	<div class="block" data-db="{id}">
		<button class="active empty" data-name="empty">empty</button>
		<button class="setting">Clear<span>empty</span></button>
	</div>
</script>

<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
<script>
	function logit(m) {
		$("pre").append(m+"\n");
	}

	$(function() {
		var tmpl = $("#tmpl-block").html();
		
		for (a=1; a <= 25; a+=1) {
			var thisTmpl = tmpl.replace("{id}",a);
			$("#stage").append(thisTmpl);
		}
	
		$(".action").on("click",function() {
			$("#stage .active").removeClass("on");
			$("#stage .active").toggle();
			$("#stage .setting").toggle();
			
		});
		
		$(".setting").on("click", function() {
			var task   = $(this);
			var parent = $(task.parent());
			
			task.find("span").html("empty");
			parent.find(".active").data("name","empty").addClass("empty").html("empty");
		});
	
		$(".active").on("click", function() {
			var task   = $(this);
			var parent = $(task.parent());
			var name   = task.data("name");
			
			if (name == "empty") {
				var name = prompt("Give this task a name");
				
				if (name != "") {
					task.data("name",name);
					task.html(name);
					parent.find("span").html(name);
					task.removeClass("empty");
				} else {
					task.data("name","empty");
					task.html(name);
					task.addClass("empty");
				}
			} else {
				if (task.hasClass("on")) { 
				task.removeClass("on"); 
			} else {
				$(".active").removeClass("on");
				task.addClass("on");
				logit(name);
			}
			}
			
			
			
		});
		//$("audio").attr("src","beep.mp3");
	});
</script>
</body>
</html>