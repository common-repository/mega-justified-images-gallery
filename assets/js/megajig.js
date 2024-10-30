! function($) {

	"use strict";

	function log(str) {
		console.log(str);
	};

	window.megajigs = [];

	var MegaJig = function(el, options) {
		var self = this;
		// chosen element to manipulate text
		this.el = $(el);

		// options
		this.options = $.extend({}, $.fn.MegaJig.defaults, options);

		// add a delay before typing starts
		this.min_height = parseInt(this.options.min_height,10);
		this.max_rows = parseInt(this.options.max_rows,10);
		this.gap = this.options.gap ? parseInt(this.options.gap,10) : 5;
		this.min_height_map = this.options.min_height_map || [];
		this.gap_wrapper = this.options.gap_wrapper ? this.gap : 0;
		this.placeholder = this.options.placeholder || 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsQAAA7EAZUrDhsAAAANSURBVBhXYzh8+PB/AAffA0nNPuCLAAAAAElFTkSuQmCC';

		//find all images
		this.photos = [];

		this.rows = [];

		this.totalWidth = 0;

		this.lightbox_open = false,

		this.rendering = false;

		if(typeof this.options.getSize == 'undefined')
			this.getSize = function (img) {
				return {'width' : img.naturalWidth, 'height' : img.naturalHeight};
			};
		else
			this.getSize = this.options.getSize;
		this.viewer_class = this.options.viewer_class ? this.options.viewer_class : '';
		$(el).addClass(this.viewer_class + ' megajig-viewer');
		// All systems go!
		this.init();

		return this;
	};

    MegaJig.prototype = {

		constructor: MegaJig,

		init: function() {
			this.layout();
		},

		updateOptions : function (options) {
			this.options = $.extend({}, $.fn.MegaJig.defaults, options);

			this.min_height = parseInt(this.options.min_height,10);
			this.max_rows = parseInt(this.options.max_rows,10);
			this.gap = this.options.gap ? parseInt(this.options.gap,10) : 0;
			this.min_height_map = this.options.min_height_map ? this.options.min_height_map : [];
			this.gap_wrapper = this.options.gap_wrapper ? this.gap : 0;
			this.placeholder = this.options.placeholder || 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsQAAA7EAZUrDhsAAAANSURBVBhXYzh8+PB/AAffA0nNPuCLAAAAAElFTkSuQmCC';

		},

		append : function (attachment, add = true) {
			var self = this,
				placeholder, $link, $img, tmpImg = new Image(), data, tmp,
				img_data = {
					src : attachment.url,
					class : 'megajig-item-img',
					alt : attachment.title
				},
				link_data = {
					href : attachment.url,
					target : '',
					class : 'megajig-item-link',
					rel : 'prettyPhoto[magejig]',
					title : attachment.description
				};

			if(typeof attachment.custom_url !== 'undefined' && attachment.custom_url != ''){
				tmp = attachment.custom_url.split('|');
				link_data.href = tmp[0];
				link_data.target = tmp[1];
				link_data.rel = '';
			}


			placeholder = $('<div class="megajig-item '+ self.options.item_class +'"></div>');
			$img = $('<img ' + this.to_attr(img_data) +'/>');
			$link = $('<a ' + this.to_attr(link_data) +'></a>');
			$img.appendTo($link);
			$link.appendTo(placeholder);

			tmpImg.onload = function(){
				placeholder.addClass('megajig-loaded');
			};

			if(typeof self.options.render_item === 'function')
				self.options.render_item(placeholder);

			tmpImg.src = img_data.src;

			data = {
				el : placeholder,
				img : $img,
				link : $link,
				width : attachment.width,
				height : attachment.height,
				src : img_data.src,
				assigned: true,
				info : attachment
			};

			if(add)
				self.photos[self.photos.length] = data;
			else
				return data;

		},

		to_attr(obj){
			return Object.keys(obj).map(function (key) { return key + '="' + obj[key] + '"'; }).join(' ');
		},

		layout : function () {

			var self = this;

			[].forEach.call(self.options.items_json, function (item, index) {
				self.append(item);
			});

			self.prepare();
		},

		prepare : function () {

			var self = this, i=0, rowWidth = 0, newRow = true, iWidth = 0, row = {}, photo ,
				limit = self.photos.length, rect, borderw, outer_keys = ['padding-left', 'padding-right', 'border-left', 'border-right'], outer;

			self.el.css({'padding-left': self.gap_wrapper + 'px','padding-right': self.gap_wrapper + 'px'});

			//get actual width of wrapper
			rect = self.el.get(0).getBoundingClientRect();

			if (rect.width) {
				// `width` is available for IE9+
				self.width = rect.width;
			} else {
				// Calculate width for IE8 and below
				self.width = rect.right - rect.left;
			}

			if(self.gap_wrapper > 0)
				self.width -= self.gap_wrapper*2;

			borderw = self.el.css("border-left-width");

			self.width -= parseInt(borderw, 10)*2;

			if(typeof self.min_height_map == 'object'){

				var maps = Object.keys(self.min_height_map).map(function(e) {
				  return [Number(e)];
				});

				maps.sort(function(a,b) {
				    return a-b;
				});

				for(var scr in maps){
					self.min_height = self.min_height_map[maps[scr]];
					if(maps[scr] > self.width)
						break;
				}
			}


			self.rows = [];

			rowWidth = 0;

			row = {items : [], width : 0, trueWidth: 0};

			while( i < limit) {

				if(self.photos[i] == undefined){
					i++;
					continue;
				}

				photo = self.photos[i];
				outer = 0;
				outer_keys.map(function (key){
					var outer_val = parseInt(photo.el.css(key));
					if(!isNaN(outer_val))
						outer += outer_val;
				});

				iWidth = (this.min_height / photo.height) * (photo.width + outer);

				photo.rewidth = iWidth;

				photo.reheight = this.min_height;

				if( (rowWidth + iWidth) < (self.width + self.gap)){

					rowWidth += iWidth + self.gap;

					row.items.push(photo);

				}else{
					// push old row
					row.width = rowWidth - self.gap;

					this.prepareRow(row);

					this.rows.push(row);

					// and init new row
					rowWidth = iWidth + self.gap;

					row = {items : [], width : 0, trueWidth: 0};

					row.items.push(photo);
				}
				i++;
			}

			if(rowWidth > 0){

				if(row.items.length < 2)
					row.width = self.width;
				else
					row.width = rowWidth - self.gap;

				self.prepareRow(row);

				self.rows.push(row);
			}

			self.display();
		},

		prepareRow : function (row) {

			var self = this,
				gaps = row.items.length - 1,
				ratio = 0,
				trueWidth = 0,
				viewWidth = self.width - gaps * self.gap,
				newHeight = (viewWidth * self.min_height)/(row.width - gaps * self.gap);

			[].forEach.call(row.items, function (item) {

				item.rewidth = (newHeight * item.rewidth)/item.reheight;
				item.reheight = newHeight;

				trueWidth += item.rewidth + self.gap;

				//assign src to attribute src image
				if(!item.assigned){

					item.img.attr('src', item.src);

					item.assigned = true;
				}

			});

			row.trueWidth = trueWidth;

		},

		display : function () {
			var self = this, tmp, cindex = 0, rindex = 0, top=0;

			self.el.html('');

			tmp = $("<div></div");

			[].forEach.call(self.rows, function (row) {

				var rowHTML = $('<div class="megajig-row"></div>'),
					left = 0;

				[].forEach.call(row.items, function (item, index) {

					var gr = self.gap,
						gb = self.gap,
						has_content,
						content = item.el.find('.megajig-content'),
						title = item.el.find('.megajig-title'),
						description = item.el.find('.megajig-description');
					if(index == row.items.length -1){
						gr = 0;
						rowHTML.css({height: (item.reheight + gb) + 'px'});
					}

					rowHTML.append(item.el);

					$(item.el).data('index', cindex).css({
						'height': item.reheight + 'px',
						'width': item.rewidth + 'px',
						'margin-right': gr + 'px',
						'left': left + 'px',
						'top': '0px',
						'margin-bottom': gb + 'px'
					});

					left += item.rewidth + gr;
					cindex++;

					//add content box
					if(self.options.title || self.options.description){

						if(!content.get(0)){
							content = $('<div class="megajig-content"></div>');
							$(item.link).append(content).addClass('megajig-has-content');
						}

						if(self.options.title){
							if(!title.get(0)){
								title = $('<div class="megajig-title">' + item.info.title + '</div>');
								content.prepend(title);
							}
						}
						else
							content.find('.megajig-title').remove();

						if(self.options.description){
							if(!description.get(0)){
								description = $('<div class="megajig-description">' + item.info.description + '</div>');
								content.append(description);
							}
						}
						else
							content.find('.megajig-description').remove();

						$(item.el).addClass('megajig-has-content');
					}
					else {
						$(item.el).removeClass('megajig-has-content');
					}

					if(!self.options.lightbox.enable)
						item.link.attr('rel','');

				});

				if(rindex == self.rows.length -1)
					rowHTML.addClass('megajig-last-row');

				self.el.append(rowHTML);

				rindex++;

			});

			self.rendering = false;
			//prettyPhoto
			if(self.options.lightbox.enable){

				if(self.options.lightbox.slideshow)
					self.options.lightbox.slideshow = self.options.lightbox.slideshow_speed;

				self.options.lightbox.social_tools = false;
				self.el.find("a[rel^='prettyPhoto']").unbind().removeData().prettyPhoto(self.options.lightbox);
			}
			//else
			//	self.el.find('a').on('click', function (e) { e.preventDefault(); });

			if(typeof this.options.rendered == 'function')
				this.options.rendered(self.rows, self.photos);

			if(typeof this.options.complete == 'function')
				this.options.complete(self);


		},

		reindex : function () {
			var photos = [];
			[].forEach.call(this.photos, function (photo) {
				if(photo !== undefined)
					photos.push(photo);
			});
			this.photos = photos;

			this.refresh();
		},

		refresh : function () {

			this.prepare();

			if(typeof this.options.refresh == 'function'){
				this.options.refresh();
			}
		}

	};

	$.fn.MegaJig = function(options) {

		return this.each(function() {

			var $this = $(this), megajig = new MegaJig(this, options);

			$this.data('model', window.megajigs.length);

			window.megajigs.push(megajig);

			$(window).on('resize', function() {
				megajig.refresh();
		    });

		});
	};

	$.fn.MegaJig.defaults = {
		items_selector: '.megajig-item',
		min_height: 250,

		//min_height_map : {1024: 200, 439: 100, 677:150},
		gap: 5,
		complete : null,
		gap_wrapper: false,
		lightbox: {
			enable : false
		},
		item_class : '',
		viewer_class : '',
        max_rows: 0, // unlimited,
		render_item : null,
		complete : null
	};

}(window.jQuery);
