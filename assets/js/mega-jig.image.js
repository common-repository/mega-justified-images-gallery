! function($) {

	"use strict";

	function log(str) {
		console.log(str);
	};

	

	var MegaJig = function(el, options) {
		var self = this;
		// chosen element to manipulate text
		this.el = $(el);

		// options
		this.options = $.extend({}, $.fn.MegaJig.defaults, options);

		// add a delay before typing starts
		this.minHeight = parseInt(this.options.minHeight,10);
		this.maxRows = parseInt(this.options.maxRows,10);
		this.gap = parseInt(this.options.gap,10);
		this.itemsSelector = this.options.itemsSelector ? this.options.itemsSelector : '.jig-items';
		this.minHeightMap = this.options.minHeightMap ? this.options.minHeightMap : [];

		//find all images
		this.photos = [];

		this.rows = [];

		this.totalWidth = 0;

		if(typeof this.options.getSize == 'undefined')
			this.getSize = function (img) {
				return {'width' : img.naturalWidth, 'height' : img.naturalHeight};
			};
		else
			this.getSize = this.options.getSize;

		// All systems go!
		this.init();
		return this;
	};

    MegaJig.prototype = {

		constructor: MegaJig,

		init: function() {

			var self = this;
            self.preloaded();
		},

		//check preload image and prepare data
		preloaded : function () {
			
			var self = this;

			var els = this.el.find(this.itemsSelector), ready = 0;

			this.el.css({'display': 'block', 'overflow' : 'hidden'});

			[].forEach.call(els, function (item, index) {

				$(item).addClass('mage-jig-item');

				var tmpImg = new Image(),
					newitem = $(item).clone();

				$(item).remove();
				
				tmpImg.onload = function(){

					log('loaded image #' + index);

					var imgsize = self.getSize(this),
						rsw = (this.minHeight / imgsize.height) * imgsize.width ;

					self.photos[index] = {
						img : newitem,
						width : imgsize.width,
						height : imgsize.height
					};

					self.totalWidth += imgsize.width;

					ready++;

					if(ready == els.length){ self.prepare();}
					
				};

				tmpImg.src = $(item).attr('src') ;

			});
		},

		prepare : function () {

			var i=0, rowWidth = 0, newRow = true, iWidth = 0, row = {}, photo ,
				limit = this.photos.length, self = this;

			self.width = self.el.width();
			log(self.minHeightMap);
			if(typeof self.minHeightMap == 'object'){

				var maps = Object.keys(self.minHeightMap).map(function(e) {
				  return [Number(e)];
				});

				maps.sort(function(a,b) {
				    return a-b;
				});

				log('maps : ' + maps);
				for(var scr in maps){
					log('check screen' + scr);
					self.minHeight = self.minHeightMap[maps[scr]];
					if(maps[scr] > self.width)
						break;
				}
			}

			log('minHeight :' + self.minHeight);

			self.rows = [];

			while( i < limit) {

				if(newRow){
					rowWidth = 0;
					row = {items : [], width : 0};
					newRow = false;
				}
				photo = this.photos[i];

				iWidth = (this.minHeight / photo.height) * photo.width;

				photo.rewidth = iWidth;
				photo.reheight = this.minHeight;

				if( (rowWidth + iWidth) < (this.width + this.gap)){
					
					rowWidth += iWidth + this.gap;
					row.items.push(photo);

				}else{
					// push old row
					row.width = rowWidth - this.gap;
					this.prepareRow(row);
					this.rows.push(row);

					// and init new row
					rowWidth = iWidth + this.gap;
					row = {items : [], width : 0};
					row.items.push(photo);
				}
				i++;
			}

			if(rowWidth > 0){
				row.width = rowWidth;
				this.prepareRow(row);
				this.rows.push(row);
			}

			this.display();
		},

		prepareRow : function (row) {

			var gaps = row.items.length - 1,
				ratio = 0, newHeight=0, trueWidth = 0, self = this;

			newHeight = ((self.width - gaps * self.gap) * self.minHeight)/(row.width - gaps * self.gap);
			[].forEach.call(row.items, function (item) {
				item.rewidth = (newHeight * item.rewidth)/item.reheight;
				item.reheight = newHeight;
				trueWidth += item.rewidth;
			});

			if( trueWidth - self.width > 0.05 * self.width ){
	            var diff = self.width - trueWidth,
	                adjustedDiff = 0;
	            for(var l = 0 ; l < row.items.length ; l++ ){
	                var currentDiff = diff / (images.length),
	                	item = row.items[i],
	                    imageWidth = item.rewidth,
	                    imageHeight = item.reheight;
	                if( i === row.items.length - 1 ){
	                    currentDiff = diff - adjustedDiff;
	                }
	                item.rewidth = imageWidth + currentDiff;
	                item.reheight = ( imageHeight / imageWidth ) * (imageWidth + currentDiff);
	                adjustedDiff += currentDiff;
	            }
	        }



	        if(row.items.length == 1){
	        	row.items[0].rewidth = self.width;
                row.items[0].reheight = ( row.items[0].reheight / self.width ) * self.width;
	        }

		},

		display : function () {
			var self = this,
			rowHTML;

			self.el.html('');



			[].forEach.call(this.rows, function (row) {

				rowHTML = $('<div class="mage-jig-row"></div>');

				[].forEach.call(row.items, function (item, index) {
					var g = self.gap;
					if(index == row.items.length -1)
						g = 0;

					$(item.img).css({
						'visibility': 'visible',
						'opacity': 1,
						'height': item.reheight + 'px',
						'width': item.rewidth + 'px',
						'margin-right': g + 'px',
						'margin-bottom': g + 'px'
					});

					rowHTML.append(item.img);

				});
				self.el.append(rowHTML);
			});
			//self.el.html(imagesHtml);
		},

		template : function (data) {
			return '<div class="mage-jig-item" style="height:' + data.height + 'px;margin-right:' + data.gap + 'px;">' +
                    '<img class="image-thumb" src="' + data.src + '" style="width:' + data.width + 'px;height:' + data.height + 'px;" >' +
                    '</div>';
		},

		refresh : function () {
			this.prepare();
		}

	};

	$.fn.MegaJig = function(option) {
		return this.each(function() {
			log('go here');
			var $this = $(this),
                options = $this.data('mage-jig');

			var grid = new MegaJig(this, options);
			$(window).on('resize', function() {
		    	grid.refresh();
		    });

		});
	};

	$.fn.MegaJig.defaults = {
		itemsSelector: '.grid-item',
		minHeight: 250,
		minHeightMap : {1024: 200, 439: 100, 677:150},
		gap: 5,
        maxRows: 0 // unlimited
	};

	$(function() {
        jQuery(".mega-jig").MegaJig();
    });

}(window.jQuery);

