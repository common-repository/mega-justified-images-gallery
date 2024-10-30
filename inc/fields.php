<script type="text/html" id="tmpl-megajig-field-text-template">
   <div class="megajig-field">
		<label for="{{{data.id}}}">
			{{{data.label}}}
		</label>
		<input type="text" {{{data.attr}}} id="{{{data.id}}}" name="{{{data.name}}}" class="megajig-form-control megajig-param" value="{{{data.value}}}"/>
        <p>{{{data.description}}}</p>
	</div>
</script>

<script type="text/html" id="tmpl-megajig-field-textarea-template">
   <div class="megajig-field">
		<label for="{{{data.id}}}">
			{{{data.label}}}
		</label>
		<textarea {{{data.attr}}} id="{{{data.id}}}" name="{{{data.name}}}" class="megajig-form-control megajig-param">{{{data.value}}}</textarea>
        <p>{{{data.description}}}</p>
	</div>
</script>

<script type="text/html" id="tmpl-megajig-field-toggle-template">
<#
	if(data.value == 'yes')
		data.checked = true;

#>
   	<div class="megajig-field megajig-switch-field">
		<label for="{{{data.id}}}">
			{{{data.label}}}
		</label>

		<div class="megajig-toggle">
			<input type="checkbox" <# if(data.checked){ #>checked<# } #> class="megajig-param-toggle">
			<input type="hidden" value="{{{data.value}}}" {{{data.attr}}} name="{{{data.name}}}" class="megajig-param">
			<span class="megajig-toggle-label" data-on="Yes" data-off="No"></span>
			<span class="megajig-toggle-handle"></span>
		</div>
        <p>{{{data.description}}}</p>
	</div>
	<#
		data.callback = function (wrp, el, $){

			var ip = el.find('.megajig-param');

			el.find('input[type=checkbox]').on('click', function (){

				var data = {}, val;

				if(this.checked){
					val = 'yes';
					ip.val('yes');
				}else{
					val = 'no';
					ip.val('no');
				}

                ip.trigger('change');
			});
		}
	#>
</script>

<script type="text/html" id="tmpl-megajig-field-select-template">

	<div class="megajig-field">
		<label for="megajig_min_height">
			{{{data.label}}}
		</label>
		<select id="{{{data.id}}}" {{{data.attr}}} name="{{{data.name}}}" class="megajig-param">
			<#
				for( var i in data.options){
                    checked = '';
                    if(i == data.value)
                        checked = ' selected';
			#>
				<option value="{{{i}}}"{{{checked}}}>{{{data.options[i]}}}</option>
			<#
				};
			#>
		</select>
        <p>{{{data.description}}}</p>
	</div>
	<#
		data.callback = function (wrp, el, $){

		}
	#>
</script>

<script type="text/html" id="tmpl-megajig-popup-item-template">
	<div class="megajig-popup" data-index="{{{data.index}}}">
		<div class="megajig-popup-heading">{{{data.title}}}</div>
        <a href="#" class="megajig-popup-close">×</a>
        <form>
            <div class="megajig-popup-content">
                <div class="megajig-popup-fields">
                </div>
            </div>
        </form>

        <div class="megajig-popup-footer">
            <a href="#" class="megajig-popup-action" data-action="save"><?php _e('Save', 'megajig');?></a>
            <a href="#" class="megajig-popup-action" data-action="cancel"><?php _e('Cancel', 'megajig');?></a>
        </div>
	</div>
	<#
		data.callback = function (wrp, el, $){
            var handler = el.find('.megajig-popup-heading'),
                left = data.ev.pageX - el.outerWidth();

            if(left < 160) left = 160;

            //move popup to new position
            el.css('top', $(window).scrollTop() + 50 +'px');
            el.css('left', left +'px');

            mj_admin.popup.el = el;

            handler.on('mousedown', function(e){
                e.preventDefault();
                var pos = mj_admin.popup.el.offset();
                var srcX = pos.left;
                var srcY = pos.top;

                mj_admin.popup.ismousedown = true;
                mj_admin.popup.data = {
                    docw : $( document ).width(),
                    doch : $( document ).height(),
                    relX : e.pageX - srcX,
                    relY : e.pageY - srcY,
                };
            });

            el.find('.megajig-popup-close').on('click', function (e){
                e.preventDefault();
                el.remove();

            });

            el.find('.megajig-popup-action').on('click', function (e) {
                e.preventDefault();
                var action = $(this).data('action'),
                    info = [];

                switch (action) {
                    case 'save':
                        var index = el.data('index');
                        info = el.find('form').serializeArray();

                        info.map(function(obj){
                            console.log('update:' + obj.name);
                            mj_admin.attachments[index][obj.name] = obj.value;
                            mj_admin.megajig.photos[index].info[obj.name] = obj.value;
                            mj_admin.megajig.photos[index].el.find('.megajig-'+obj.name).html(obj.value);
                            mj_admin.megajig.refresh();
                            el.remove();
                        });

                        break;
                    case 'cancel':
                        el.remove();
                        break;
                }
            });

		}
	#>
</script>
<script type="text/html" id="tmpl-megajig-field-link-template">
    <#
        var value = data.value;

        if( typeof value !== 'undefined' && value != '' )
            value = value.split('|');
        else value = ['',''];
    #>
    <div class="megajig-field">
        <label for="{{{data.id}}}">
            {{{data.label}}}
        </label>
        <div class="megajig-link-action megajig-btn"><span class="dashicons dashicons-admin-links"></span>Add Link</div>
        <input type="text" id="{{{data.id}}}" name="{{{data.name}}}" class="megajig-form-control megajig-param megajig-hidden" value="{{{data.value}}}"/>
        <div class="megajig-link-val">
            Link : <span>{{{value[0]}}}</span><br/>
            Target : <span>{{{value[1]}}}</span>
        </div>
        <p>{{{data.description}}}</p>

    </div>
    <#
        data.callback = function (wrp, el, $){
            el.find('.megajig-link-action').on('click', function (e){
            e.preventDefault();

            var value = el.find('.megajig-param').val();

            if( value != '' )
                value = value.split('|');
            else value = ['',''];

            var eid = 'hidden-editor';

            wpActiveEditor = eid;

            wpLink.open();

            $('.wp-link-text-field').css({display:'none'});
            $('#wp-link-url').val( value[0] );
            if( value[1] == '_blank' )
	        	$('#wp-link-target').attr({checked: true});
            else
                $('#wp-link-target').attr({checked: false});


            $('#wp-link-submit').val('Add Link to Image').off('click').on('click', function (e){
                var url = $('#wp-link-url').val(),
                target = $('#wp-link-target').get(0).checked?'_blank':'';

                el.find('.megajig-param').val(url+'|'+target);
                el.find('.megajig-link-val').html('Link : <span>' + url + '</span><br/>Target : <span>' + target + '</span>');

                wpLink.close();
                e.preventDefault ? e.preventDefault() : e.returnValue = false;
                e.stopPropagation();
                return false;
            });

            });
        }
    #>
</script>
