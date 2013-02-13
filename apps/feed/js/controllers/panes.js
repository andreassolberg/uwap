define(function(require, exports, module) {

	var 
		panes = {},

		$ = require('jquery');

		/*
		
			<div id="panecontainer">
				<div id="navbar"></div>
				<div id="panes"></div>
			</div>

		 */



	panes.PaneController = function(el) {
	
		this.panelist = {};
		this.current = null;

		this.containerEl = el;
		// this.navbarEl = $('<div id="navbar"></div>').appendTo(el);
		this.panesEl = $('<div id="panes"></div>').appendTo(el);


		// this.navbar = new panes.NavBar(this.navbarEl, this);
	};


	panes.PaneController.prototype.get = function(id, title) {

		if (this.panelist.hasOwnProperty(id)) return this.panelist[id];
		var paneEl = $('<div class="pane"></div>').appendTo(this.panesEl);
		paneEl.hide();
		this.panelist[id] = new panes.Pane(paneEl, id, this);
		return this.panelist[id];
	}

	// Should only be called by the pane.
	panes.PaneController.prototype.iamactive = function(id) {
		if (id === this.current) return;
		if (this.current !== null) {
			this.panelist[this.current].deactivate();
		}
		this.current = id;
	}





	panes.NavBar = function(el) {
		this.el = el;
		// this.pc = pc;

		this.el.on('click', '.paneselector', function(e) {
			console.log("Click paneselector", e);
		});
	};

	panes.NavBar.prototype.set = function(entries) {
		var that = this, c;

		this.el.empty();
		for(var i= 0; i < entries.length; i++) {
			c = entries[i];
			if (i === entries.length-1) {
				this.el.append('<li class="active"><a href="#">' + c.title + '</a></li>');
			} else {
				this.el.append('<li><a href="#!' + c.href + '">' + c.title + '</a></li>');
			}
			
		}	

	}



	panes.Pane = function(el, id, pc) {
		this.pc = pc;
		this.id = id;
		this.el = el;
		this.title = null;

		this.callbacks = {
			'activate': [],
			'deactivate': []
		};


		this.el.attr('id', 'uwapfeedpane-' + id);

		this.active = false;
	}

	panes.Pane.prototype.on = function(r, callback) {
		this.callbacks[r].push(callback);
	}
	panes.Pane.prototype.emit = function(r, data) {
		$.each(this.callbacks[r], function(i, callback) {
			callback(data);
		});
	}


	panes.Pane.prototype.deactivate = function() {
		this.active = false;
		this.el.hide();
		this.emit('deactivate', true);
	}
	panes.Pane.prototype.activate = function() {
		this.pc.iamactive(this.id);
		this.active = true;
		// console.log("Is about to show element", this.el)
		this.el.show();
		this.emit('activate', true);
	}
	panes.Pane.prototype.getTitle = function() {
		if (this.title) return this.title;
		return this.id;
	}






	return panes;

});