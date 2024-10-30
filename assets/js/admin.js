
(function($) {

	"use strict";

	console.log('mj_admin started!');

	window.mj_admin = {
		data : {},
		current_options : {},
		megajig : null,
		indexs: [],
		attachments : [],
		options : [],
		presets : [],
		rendered : false,
		init : function () {

			var model, scrstatus,
				that = this;

			mj_admin.tabs.render();

			mj_admin.fire({
				handler : $('.megajig_add_images'),
				funcs : {
					':click': 'add_image',
				},
				add_image : function (e){
					e.preventDefault();
					that.media.add();
				}
			});

			mj_admin.fire({
				handler : $('.megajig-nav li:not(.megajig-action) a'),
				funcs : {
					':click': 'change_tab',
				},
				change_tab : function (e){
					e.preventDefault();
					var tid = $(this).attr('href');

					$( '.megajig-tab-content').removeClass('active');
					$( '.megajig-nav li').removeClass('active');
					$( this ).closest('li').addClass('active');
					$( tid ).addClass('active');

				}
			});




			scrstatus = $('.megajig-screens-status');


			mj_admin.fire({
				handler: $('#megajig-screens .megajig-screen-size'),
				funcs : {
					':mouseover' : 'change_text',
					':mouseout' : 'clear_text',
					':click' : 'switch_text',
				},
				change_text : function (e){
					e.preventDefault();
					scrstatus.data('oldtext', scrstatus.html());
					scrstatus.html($(this).attr('title'));
				},
				clear_text : function (e){
					scrstatus.html(scrstatus.data('oldtext'));
				},
				switch_text : function (e){
					var ssize = $(this).data('screen');

					e.preventDefault();

					scrstatus.html($(this).attr('title')).data('oldtext',$(this).attr('title'));

					$('#megajig-screens .megajig-screen-size').removeClass('active');

					$(this).addClass('active');

					if(ssize == 'auto')
						ssize = '100%';
					else
						ssize += 'px';

					setTimeout(function () {
			            that.preview.livechange();
			        }, 300);
			        $('#megajig-viewer').css({'width': ssize});
				}
			});

			mj_admin.fire({
				handler: $('.megajig-expand-viewer'),
				funcs : {
					':click' : 'fullscreen'
				},
				fullscreen : function (e){
					e.preventDefault();
					$(this).toggleClass('active');
					setTimeout(function () {
			            mj_admin.preview.livechange();
			        }, 300);
					$('#megajig-preview').toggleClass('expand');
				}
			});

			mj_admin.fire({
				handler : $(".megajig-btn-save"),
				funcs : {
					':click' : 'save_data'
				},
				save_data : function (e) {

					e.preventDefault();

					$('.megajig-items').val(JSON.stringify(mj_admin.attachments));
					$('#megajig-settings').val(JSON.stringify(mj_admin.data));
					var options = that.settings.generate();
					$('#megajig-options').val(JSON.stringify(options));
					$('#megajig-form').submit();
				}
			});

			mj_admin.fire({
				handler : $(document),
				funcs : {
					':mousemove' : 'change_popup_pos'
				},
				change_popup_pos : function (e) {

					if(mj_admin.popup.ismousedown)
	                {
	                    var targetw = mj_admin.popup.el.outerWidth(),
							targeth = mj_admin.popup.el.outerHeight(),
							maxX = mj_admin.popup.data.docw - targetw - 10,
							maxY = mj_admin.popup.data.doch - targeth - 10,
							mouseX = e.pageX,
							mouseY = e.pageY,
							diffX = mouseX - mj_admin.popup.data.relX,
							diffY = mouseY - mj_admin.popup.data.relY;

	                    // check if we are beyond document bounds ...
	                    if(diffX < 0)   diffX = 0;
	                    if(diffY < 0)   diffY = 0;
	                    if(diffX > maxX) diffX = maxX;
	                    if(diffY > maxY) diffY = maxY;

	                    mj_admin.popup.el.css('top', (diffY)+'px');
	                    mj_admin.popup.el.css('left', (diffX)+'px');
	                }
				}
			});

			mj_admin.fire({
				handler : $(window),
				funcs : {
					':mouseup' : 'release_popup'
				},
				release_popup : function (e) {
					mj_admin.popup.ismousedown = false;
				}
			});

			mj_admin.current_options.item_class = 'sortable';
			mj_admin.current_options.complete = function (megajig) {
				that.sortable.init();
			}

			mj_admin.current_options.render_item = function (item) {
				//add controller actions for items
				var controlls = $('<div class="megajig-controlls">\
				<a href="#" class="megajig-controll" data-action="edit"><span class="megajig-controll-label">Edit Image Info </span><span class="dashicons dashicons-edit"></span></a>\
				<a href="#" class="megajig-controll" data-action="remove"><span class="megajig-controll-label">Remove Image</span><span class="dashicons dashicons-trash"></span></a>\
				<a href="#" class="megajig-controll" data-action="swap"><span class="megajig-controll-label">Change Image </span><span class="dashicons dashicons-controls-repeat"></span></a>\
				</div>');

				$(item).append(controlls);

			};

			jQuery("#megajig-viewer").MegaJig(mj_admin.current_options);

			that.attachments = that.current_options.items_json;

			that.sortable.init();

			model = $('#megajig-viewer').data('model');

			that.megajig = window.megajigs[parseInt(model)];

		},

		fire: function (cfg){
			for(var ev in cfg.funcs){
				var func;
				if( typeof cfg.funcs[ev] == 'function' )
					func = cfg.funcs[ev];
				else if( typeof cfg[cfg.funcs[ev]] == 'function' )
					func = cfg[cfg.funcs[ev]];
				else continue;

				ev = ev.split(':');

				if(ev[0] === '')
					cfg.handler.on(ev[1], cfg, func);
				else
					cfg.handler.find(ev[0]).off(ev[1]).on(ev[1], cfg, func);
			}
		},

		settings : {

			update : function (data) {
				var options = mj_admin.data,
					nstr = data.name.split('|'), tabid, name;

				tabid = nstr[0];
				name = nstr[1];

				if(typeof options[tabid] == 'undefined')
					options[tabid] = {};

				options[tabid][name] = data.value;

			},

			reindex : function () {

				var attachments = [], photos = [];

				$('#megajig-viewer').find('.megajig-item').each(function () {
					var index = $(this).data('index');

					attachments.push(mj_admin.attachments[index]);
					photos.push(mj_admin.megajig.photos[index]);
				});

				mj_admin.attachments = attachments;
				mj_admin.megajig.photos = photos;
				mj_admin.megajig.refresh();

			},

			convert : function (settings) {
				for(var i in settings){
					if(settings[i] == "yes" || settings[i] == "no")
						settings[i] = (settings[i] == 'yes');

					if(typeof settings[i] == 'string' && !isNaN(settings[i]))
						settings[i] = parseInt(settings[i]);

				}
			},

			generate : function () {

				//update options
				var settings = $.extend({}, mj_admin.current_options, mj_admin.data.layout),
					viewer_class = [];

				mj_admin.settings.convert(settings);
				//generate presets
				for(var tab in mj_admin.data)
					for(var i in mj_admin.data[tab])
						if(mj_admin.presets.indexOf(i)> -1)
							viewer_class.push('mjpreset-' + i + '-' + mj_admin.data[tab][i]);

				if(viewer_class.length > 0)
					settings['viewer_class'] = viewer_class.join(' ');

				delete settings.complete;
				delete settings.refresh;
				delete settings.render_item;
				delete settings.items_json;

				return settings;

			}

		},

		media : {

			escapse : ['filename', 'link', 'alt', 'author', 'name', 'status', 'dateFormatted', 'nonces', 'update', 'delete', 'edit', 'editLink', 'meta', 'authorName', 'uploadedToLink', 'uploadedToTitle', 'filesizeInBytes', 'filesizeHumanReadable', 'orientation', 'sizes', 'compat', 'icon', 'uploadedTo', 'caption', 'date', 'modified', 'menuOrder', 'mime', 'subtype', 'type'],

			add : function () {

				var handler = wp.media.frames.file_frame = wp.media({
						title: 'Choose Images',
						button: {
							text: 'Choose Images'
						},
						multiple: true
					});


				handler.on('select', function () {

					var selections = handler.state().get('selection');

					selections.map( function( attachment, ind ) {
						attachment = attachment.toJSON();
						mj_admin.media.escapse.map(function (field){
							delete attachment[field];
						});
						mj_admin.attachments.push(attachment);
						mj_admin.megajig.append(attachment);
						if(ind == selections.length-1)
							mj_admin.megajig.refresh();
					});
					setTimeout(function (){
						console.log('call');
						mj_admin.megajig.refresh();
					},100);


				});

				handler.open();

			},

			replace : function (index) {

				var handler = wp.media.frames.file_frame = wp.media({
					title: 'Choose Image',
					button: {
						text: 'Replace'
					},
					multiple: false
				}),
				id = mj_admin.attachments[index];

				handler.on('open', function () {
					var selection = handler.state().get('selection');
					selection.add( wp.media.attachment(id) );
				});


				handler.on('select', function () {
					var attachment = handler.state().get('selection').first().toJSON();
					mj_admin.media.escapse.map(function (field){
						delete attachment[field];
					});
					mj_admin.attachments[index] = attachment;
					mj_admin.megajig.photos[index] = mj_admin.megajig.append(attachment, false);
					mj_admin.megajig.refresh();
				});

				handler.open();
			}
		},

		preview : {

			livechange : function () {

				if(mj_admin.megajig == null)
					return;

				var settings, lightbox, options = {};

				//update options
				settings = $.extend({}, mj_admin.current_options, mj_admin.data.layout);
				lightbox = $.extend({}, mj_admin.current_options.lightbox, mj_admin.data.lightbox);

				mj_admin.settings.convert(settings);
				mj_admin.settings.convert(lightbox);

				settings.lightbox = lightbox;

				settings.complete = function (megajig) {
					$(megajig.el).find('.megajig-item').addClass('sortable');
					mj_admin.sortable.init();
				}

				mj_admin.current_options = settings;

				mj_admin.megajig.updateOptions(settings);

				mj_admin.megajig.refresh();

			},

			style : function () {

			}
		},

		template : function (wrp, data) {

			var field = wp.template( "megajig-field-" + data.type + "-template"),
				template = $(field( data ));

			wrp.append(template);

			if(typeof data.callback !== 'undefined')
				data.callback(wrp, template, $);
		},

		popup : {

			el: null,
			data : {},
			ismousedown : false,
			open : function (wrp, data) {

				var popup = wp.template( "megajig-popup-" + data.type + "-template"),
					template = $(popup( data ));

				//remove all open popup
				wrp.find('.megajig-popup').remove();
				wrp.append(template);

				setTimeout(function (){
					template.addClass('open');
				},10);

				if(typeof data.callback !== 'undefined')
					data.callback(wrp, template, $);

				//render settings fields
				data.options.map(function (item){
					mj_admin.template(template.find('.megajig-popup-fields'), item);
				});

			}
		},

		style : {

			update : function (data){
				var that = $(data.el),
					style_key = ['css', 'preset'];


				style_key.map(function (key){

					if(that.data(key) == true){
						var selector = that.data('selector'),
							parram = that.data('param'),
							$el;
						if(mj_admin.megajig.el.is(selector)){
							$el = mj_admin.megajig.el;
							data.viewer_class = 'mj'+key+'-'+ parram + '-' + data.val;
						}

						else
							$el = mj_admin.megajig.el.find(selector);

						$el.each(function (e){
							var regex = new RegExp('\\bmjpreset-'+ parram + '-' + '\\S+', "g");

							$(this).removeClass(function (index, css) {
								return (css.match(regex) || []).join(' '); // removes anything that starts with "page-"
							});

							$(this).addClass('mj'+key+'-'+ parram + '-' + data.val);
						})
					}
				});

				mj_admin.settings.update(data.el);

				if(that.data('live') == true) mj_admin.preview.livechange();
			}
		},

		tabs : {
			create : function (wrp, id, data) {

				var tab_content = $('<div class="megajig-tab-content postbox" id="' + id + '">'),
					tab_item = $('<li><a href="#'+ id +'">' + id + '</a></li>'),
					group = $('<div class="megajig-group-field"></div>'),
					tabs_content = wrp.find('.megajig-tabs'),
					settings = [];

				if(typeof mj_admin.data[id] !== 'undefined')
					settings = mj_admin.data[id];

				wrp.find(".megajig-nav").append(tab_item);

				tab_content.appendTo(tabs_content);

				for(var i in data){

					var item = data[i];

					if(typeof item.type == 'undefined'){
						//render group
						var grp = group.clone();

						if(isNaN(i)) grp.append('<div class="megajig-heading">'+i.replace('_', ' ')+'</div>');

						for(var g in item){
							var itm = item[g];

							if(typeof settings[itm.name] !== 'undefined')
								itm.value = settings[itm.name];

							this.render_item({
								id : id,
								tab_content: grp,
								item: itm
							});
						}
						grp.appendTo(tab_content);
					}
					else{
						this.render_item({
							id : id,
							tab_content: tab_content,
							item: item
						});
					}
					//assign on change action
					tab_content.find('select, input:not(:checked)').off('change').on('change', function () {

						if(this.name == '') return;

						var that = $(this),
							val = this.value,
							data = {
								name : this.name,
								val : this.value,
								el : this
							};

						mj_admin.style.update(data);

					});

				}

				mj_admin.fire({
					handler: tab_item,
					funcs : {
						'a:click' : 'active_tab'
					},
					active_tab : function (e){
						e.preventDefault();
						$('.megajig-tab-content').removeClass('active');
						$('.megajig-nav li').removeClass('active');
						$(this).closest('li').addClass('active');
						tab_content.addClass('active');
					}
				});

			},

			render_item : function (data){
				data.item.param = data.item.name;
				data.item.name = data.id + '|' + data.item.name;
				var param_attr = [],
					param_key =['live', 'preset', 'css', 'selector', 'param'];

				param_key.map(function (key){
					if(data.item[key] !== undefined && data.item[key] !== false){
						param_attr.push('data-' + key + '="' + data.item[key] +'"');
						if(key == 'preset') mj_admin.presets.push(data.item.param);
					}
				});

				data.item.attr = param_attr.join(' ');

				mj_admin.template(data.tab_content, data.item);
				if(data.item['live'] !== 'undefined' && data.item.live)
					mj_admin.settings.update(data.item);
			},

			render : function () {
				var wrp = $('.megajig-settings');

				for(var t in megajig_settings){
					mj_admin.tabs.create(wrp, t, megajig_settings[t]);
				}
			}
		},

		//drag item to change position
		sortable : {
			el : null,
			el_drag: null,
			items : null,
			callback : null,
			init : function (){

				var items = $("#megajig-viewer").find('.sortable');
				mj_admin.sortable.items = items;
				[].forEach.call(items, function(item) {
					for(var e in mj_admin.sortable.events){
						$(item).off(e);
						item.addEventListener( e, mj_admin.sortable.events[e], false);
					}
				});

				mj_admin.fire({
					handler : $('#megajig-viewer .megajig-controll'),
					funcs : {
						':click' : 'actions'
					},
					edit : function (data){
						var edit_settings = {
							title : 'Image Settings',
							type : 'item',
							index : data.index,
							ev : data.e,
							options : [
								{
									type : 'text',
									name : 'title',
									id : 'title',
									label : 'Title',
									value : data.current.title
								},
								{
									type : 'textarea',
									name : 'description',
									id : 'description',
									label : 'Description',
									value : data.current.description
								},
								{
									type : 'link',
									name : 'custom_url',
									id : 'url',
									label : 'Custom Link',
									value : data.current.custom_url
								}
							]

						}
						mj_admin.popup.open($('body'), edit_settings);
					},

					remove : function (data){
						if (confirm("Are you sure want to remove image this gallery?") == true) {
							var attachments = [],
								photos = [];

							[].forEach.call(mj_admin.attachments, function (attachment, i) {
								if(i != data.index) attachments.push(attachment);
							});

							[].forEach.call(mj_admin.megajig.photos, function (photo, i) {
								if(i != data.index) photos.push(photo);
							});

							mj_admin.megajig.photos = photos;
							mj_admin.attachments = attachments;
							mj_admin.megajig.refresh();
						}
					},

					swap : function (data){
						var item = data.el.closest('.megajig-item'),
							index = item.data('index');

						mj_admin.media.replace(index);
						mj_admin.megajig.reindex();
						mj_admin.sortable.init();
					},

					actions : function(e){
						e.preventDefault();

						var action = $(this).data('action'),
							item = $(this).closest('.megajig-item'),
							index = item.data('index'),
							current = mj_admin.attachments[index];

						if(typeof e.data[action] !== 'undefined') e.data[action]({e:e, current: current, index : index, el: $(this)});
					}
				});

			},

			is_before : function (el, cel){
				var items = $('#megajig-viewer .megajig-item'),
					founde = false, foundc = false, is_before = false;

				[].forEach.call(items, function (e){
					if($(e).is(cel) & founde)
						is_before = true;
					if($(e).is(cel) & !founde)
						is_before = false;

					if($(el).is(e))
						founde = true;
					if($(cel).is(e))
						foundc = true;

				});
				return is_before;
			},

			events : {
				dragstart : function (e) {
					console.log("dropstart");
					mj_admin.sortable.el_drag = $(e.target).closest('.megajig-item')[0];
					e.dataTransfer.effectAllowed = 'move';
					///e.dataTransfer.setData('text/html',e.target);
					e.dataTransfer.setDragImage(this, 100, 100);
					mj_admin.sortable.el_drag.style.opacity = '0.7';
 				},

				dragenter : function (e) {
					$(this).addClass('over');
				},

				dragover : function (e) {
					if (e.preventDefault) {
						e.preventDefault();
					}
					$(this).addClass('over');
					e.dataTransfer.dropEffect = 'move';
					return false;
				},

				dragleave : function (e) {
					$(this).removeClass('over');
				},

				dragend : function (e) {
					$('.megajig-item').removeClass('over');
					mj_admin.settings.reindex();
				},

				drop : function (e) {
					e.preventDefault();
					mj_admin.sortable.el_drag.style.opacity = '1.0';
					if (e.stopPropagation) e.stopPropagation();

					if (mj_admin.sortable.el_drag != this) {
						if(mj_admin.sortable.is_before(mj_admin.sortable.el_drag, this))
							$(this).after(mj_admin.sortable.el_drag);
						else
							$(this).before(mj_admin.sortable.el_drag);
					}
					return false;

				},
			},

		}

	}

})(jQuery);
