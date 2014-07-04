define(function(require, exports, module) {

	var 
		$ = require('jquery'),

		hb = require('uwap-core/js/handlebars')
		;

	// var authzclientsTmplText = require('uwap-core/js/text!templates/components/authorizedAPIs.html');
	// var authzclientsTmpl = hb.compile(authzclientsTmplText);



	var ListPager = function(element, limit, callback) {

		this.element = element;
		this.callback = callback;
		this.limit = limit;

		this.currentPage = 1;

		this.element.on('click', '.selectPage', $.proxy(this.select, this));

	};

	ListPager.prototype.select = function(e) {
		e.preventDefault(); e.stopPropagation();

		var page = $(e.currentTarget).data('page');
		if (!this.currentPage) return;

		this.currentPage = page;

		console.log("Page is ", this.currentPage);

		var query = this.getQuery();

		this.callback(query);
	};

	ListPager.prototype.getQuery = function() {


		var startsWith = (this.currentPage-1)*this.limit;

		var query = {startsWith: startsWith};
		return query;
	};

	ListPager.prototype.setSet = function(set) {
		this.set = set;

		var meta = this.set.getPager();



		var noPages = 1 + Math.floor(meta.count / this.limit);
		var curPage = 1 + Math.floor(meta.startsWith / this.limit);

		console.log("meta", meta, "noPages", noPages, "curPage", curPage);

		if (noPages < 1) {
			this.disable(); 
			return;
		}

		this.draw(noPages, curPage);
	}

	ListPager.prototype.disable = function() {
		this.element.empty();
	}

	ListPager.prototype.draw = function(no, current) {

		console.log("DRAW", no, current, this.element)

		var html = '<ul class="pagination">';

		for(var i = 1; i <= no; i++) {

			if (i > 20) {
				html += '<li class="disabled"><a href="#">&raquo;</a></li>';
				break;
			}

			if (current === i) {
				html += '<li class="active"><a class="selectPage" href="#">' + i + ' <span class="sr-only">(current)</span></a></li>';
			} else {
				html += '<li><a class="selectPage" data-page="' + i + '" href="#">' + i + ' <span class="sr-only">(current)</span></a></li>';
			}
		}

		html += '</ul>';

		this.element.empty().append(html);

		
	//   <li class="disabled"><a href="#">&laquo;</a></li>
	//   <li class="active"><a href="#">1 <span class="sr-only">(current)</span></a></li>
	//   ...
	// </ul>

	}


	return ListPager;


});