(function ($) {
	Array.prototype.insert = function(index) {
		this.splice.apply(this, [index, 0].concat(
		Array.prototype.slice.call(arguments, 1)));
		return this;
	};

	Array.prototype.remove = function(from, to) {
		var rest = this.slice((to || from) + 1 || this.length);
		this.length = from < 0 ? this.length + from : from;
		return this.push.apply(this, rest);
	}

	Date.prototype.isValid = function () {
		// An invalid date object returns NaN for getTime() and NaN is the only
		// object not strictly equal to itself.
		return this.getTime() === this.getTime();
	};

	function isInt(value) {
		return !isNaN(value) && (function(x) { return (x | 0) === x; })(parseFloat(value))
	}

	function CADBFieldEditor(element,options) {
		var self = this;
		this.settings = $.extend({}, $.fn.cadbFieldEditor.defaults, options);

		this.Root = jQuery(element);

		var transEndEventNames = {
			'WebkitTransition' : 'webkitTransitionEnd',
			'MozTransition'    : 'transitionend',
			'transition'       : 'transitionend'
		}

		this.transitionEnd = transEndEventNames[ Modernizr.prefixed('transition') ];

		self.initField();
//		self.keyhandle();
	}

	CADBFieldEditor.prototype = {
		initField: function() {
			var self = this;
			self.fields = [];
			self.fields_index = {};
			this.listContainer = this.Root.find('ol.field-list');
			this.table = this.listContainer.attr('data-table');
			var addConsole = jQuery(self.settings.template.addfieldconsole);
			this.listContainer.find('li.field').each(function(i) {
				var $this = jQuery(this);

				var item_addConsole = addConsole.clone();
				item_addConsole.attr('target-index', i );
				self.handleClick(item_addConsole,'add');
				item_addConsole.appendTo($this);

				var obj = {};

				obj.obj = $this;
				obj.obj.attr('data-index',i);
				obj.fid = $this.attr('data-fid');
				obj.idx = $this.attr('data-idx');
				obj.subject = $this.find('label').text();
				obj.iscolumn = ( $this.hasClass('iscolumn') ? 1 : 0);
				obj.multiple = ( $this.hasClass('multiple') ? 1 : 0);
				obj.required = ( $this.hasClass('required') ? 1 : 0);
				obj.active = ( $this.hasClass('active') ? 1 : 0);
				obj.autocomplete = ( $this.hasClass('autocomplete') ? 1 : 0);
				obj.system = ( $this.hasClass('system') ? 1 : 0);
				obj.type = $this.attr('data-type');
				obj.cid = $this.attr('data-cid');
				obj.indextype = $this.attr('data-indextype');

				self.handleClick(obj.obj.find('.inner'),'modify');

				self.fields.push(obj);
				self.fields_index[obj.fid] = i;
			});
			this.listContainer.sortable({
				group: 'field-list',
				pullPlaceholder: false,
				onDrop: function  ($item, container, _super) {
					var $clonedItem = jQuery('<li/>').css({height: 0});
					$item.before($clonedItem);
					$clonedItem.animate({'height': $item.height()});

					$item.animate($clonedItem.position(), function  () {
						$clonedItem.detach();
						_super($item, container);
						var old_idx = parseInt($item.attr('data-index'));
						var new_idx = parseInt(self.listContainer.find('li.field').index($item));
						if(old_idx != new_idx) {
							self.reIndex();
							self.updateIndex();
						}
					});
				},

				onDragStart: function ($item, container, _super) {
					var offset = $item.offset(),
					pointer = container.rootGroup.pointer;

					adjustment = {
						left: pointer.left - offset.left,
						top: pointer.top - offset.top
					};

					_super($item, container);
				},
				onDrag: function ($item, position) {
					$item.css({
						left: position.left - adjustment.left,
						top: position.top - adjustment.top
					});
				}
			});
			this.listContainer.sortable('disable');
			this.Root.find('i.sortable').click(function(e) {
				$this = jQuery(this);
				if($this.hasClass('fa-chain')) {
					if(self.field_form_activated === false) {
						$this.removeClass('fa-chain').addClass('fa-chain-broken').css('color', '#595757');
						self.listContainer.sortable('enable');
					}
				} else {
					$this.removeClass('fa-chain-broken').addClass('fa-chain').css('color', '#cccccc');
					self.listContainer.sortable('disable');
				}
			});
			this.form = this.Root.find('.field-form-item').clone();
			this.form.addClass('collapsed');
			this.form_height = this.Root.find('.field-form-item').outerHeight();
			this.Root.find('.field-form-item').remove();

			this.tform = this.Root.find('.taxonomy-terms-container').clone();
			this.tform.addClass('collapsed');
			this.Root.find('.taxonomy-terms-container').remove();

			this.field_form_activated = false;
		},

		handleClick: function(element,mode) {
			var self = this;

			element.click(function(e) {
				var $this = jQuery(this);
				if($this.hasClass('inner')) {
					var $element = $this.parents('li.field');
				} else {
					var $element = $this;
				}
				var $li = $this.parents('li.field');
				var $parent = $this.parents('li.field').parent();
				if(mode == 'modify')
					var $id = 'field-form-item-'+$element.attr('data-fid');
				else
					var $id = 'field-form-item-0';
				if($this.hasClass('extended')) {
					self.removeFormByFid((mode == 'modify' ? $element.attr('data-fid') : 0));
				} else {
					self.Root.find('.field-form-item').each(function(i) {
						self.removeForm( jQuery(this) );
					});
					self.cform = self.form.clone();
					var t = $li.position().top - 20;
					if(mode == 'add') {
						t += $this.position().top;
					}
					var maxHeight = (jQuery(window).height() - $parent.offset().top - 60 );
					if( ( t + self.form_height ) > maxHeight ) {
						t = maxHeight - self.form_height;
						self.cform.find('legend').css({
							'top': ( self.form_height - ( maxHeight - $element.position().top ) - 10 )+'px'
						});
					}
					var l = $li.position().left + $element.width() + 20;
					var idx = $element.attr('data-index');
					self.cform.attr('id',$id);
					if(mode == 'modify') {
						self.cform.attr('data-fid',$element.attr('data-fid'));
						self.cform.attr('data-index',idx);
						self.cform.attr('data-target', idx);
					} else {
						self.cform.attr('data-fid',0);
						self.cform.attr('data-index',( parseInt($element.attr('target-index')) + 1 ) );
						self.cform.attr('data-target', $element.attr('target-index') );
					}
					self.cform.css({
						'left': l + 'px',
						'top': t + 'px'
					});
					self.initItemForm(idx,self.cform,mode);
					self.cform.find('i.fa-close').click(function(e) {
						self.removeFormByFid(jQuery(this).parents('.field-form-item').attr('data-fid'));
					});
					self.cform.find('button.close').click(function(e) {
						e.preventDefault();
						self.removeFormByFid(jQuery(this).parents('.field-form-item').attr('data-fid'));
					});
					self.Root.append(self.cform);
					self.field_form_activated = true;
					$this.addClass('extended');
					setTimeout(function() {
						self.cform.removeClass('collapsed');
					}, 10);
				}
			});
		},

		initItemForm: function(idx,f,mode) {
			var self = this;
			var item = this.fields[idx];
			f.find('input#fid').val( f.attr('data-fid') );
			f.find('input#mode').val(mode);
			if(mode == 'modify') {
				f.find('input#idx').val( parseInt(f.attr('data-target')) );
				f.find('input#subject').val(item.subject);
				if(item.iscolumn) {
					f.find('input#iscolumn').attr('checked',true);
				}
				if(item.iscolumn) {
					f.find('.field-form').addClass('iscolumn');
				}
			} else {
				f.find('input#idx').val( ( parseInt(f.attr('data-target')) + 2 ) );
			}
			f.find('input#iscolumn').change(function(e) {
				if(jQuery(this).prop('checked')) {
					f.find('.field-form').addClass('iscolumn');
				} else {
					f.find('.field-form').removeClass('iscolumn');
				}
			});
			if(mode == 'modify') {
				f.find('select#field-type').val(item.type);
				if(item.type == 'taxonomy') {
					f.find('.field-form').addClass('taxonomy');
					f.find('input#multiple').attr('checked', ( item.multiple ? true : false ) );
					f.find('select#cid').val(item.cid);
				} else {
					f.find('.field-form').removeClass('taxonomy');
					f.find('input#multiple').attr('checked', false);
					this.fields[idx].multiple = 0;
				}
			}
			f.find('select#field-type').change(function(e) {
				var stype = jQuery(this).val();
				if(stype == 'taxonomy') {
					f.find('.field-form').addClass('taxonomy');
				} else {
					f.find('.field-form').removeClass('taxonomy');
					f.find('input#multiple').attr('checked', false);
				}
			});
			f.find('button.taxonomy-terms').click(function(e) {
				var $this = jQuery(this);
				e.preventDefault();
				if($this.hasClass('extended')) {
				} else {
					if(parseInt(f.find('select#cid').val()) == 0) {
						self.abortDialog('분류를 선택해주세요.',8)
						return false;
					}
					self.showTaxonomyTerms(f.attr('data-fid'),f.find('select#cid').val());
					$this.addClass('extended');
				}
			});

			this.bindAddTaxonomy(f.find('.new-taxonomy-wrapper'));

			f.find('.field-form button.delete').click(function(e) {
				e.preventDefault();
				self.deletes(f.attr('data-fid'));
			});
			if(mode == 'modify') {
				f.find('input#required').attr('checked', ( item.required ? true : false ) );
				f.find('input#active').attr('checked', ( item.active ? true : false ) );
				f.find('input#autocomplete').attr('checked', ( item.autocomplete ? true : false ) );
				f.find('select#indextype').val(item.indextype);
				f.find('.field-form button.submit').text('수정');
			} else {
				/* indextype remove class */
				f.find('.field-form button.delete').css({'display': 'none'});
			}
			f.submit(function(e) {
				e.preventDefault();
				self.saveField();
			});
		},

		removeFormByFid: function(fid) {
			var $id = 'field-form-item-'+fid;
			var f = this.Root.find('#'+$id);
			this.removeForm(f);
		},

		removeForm: function(f) {
			var self = this;
			f.addClass('collapsed');
			f.find('.field-form').bind(self.transitionEnd, function(e) {
				f.remove();
			});
			if(f.find('input#mode').val() == 'add' && f.attr('data-target') != 'undefined') {
				this.fields[parseInt(f.attr('data-target'))].obj.find('.field-console').removeClass('extended');
			} else {
				this.fields[parseInt(f.attr('data-index'))].obj.find('.inner').removeClass('extended');
			}
			this.field_form_activated = false;
		},

		updateIndex: function() {
			var self = this;
			var url = site_base_uri+"/admin/fields/resort";

			var index = JSON.stringify(this.fields_index);
			var params = "table="+this.table+"&index="+index;

			jQuery.ajax({
				url: url,
				data: params,
				dataType: 'json',
				method: 'POST',
				beforeSend: function() {
					self.loading();
				},
				success: function(json) {
					var error = parseInt(json.error);
					var message = json.message;
					self.removeLoading();
					if(error < 0) {
						self.abortDialog(message,0);
					}
				},
				error: function( jqXHR, textStatus, errorThrown ) {
					self.removeLoading();
					self.abortDialog(jqXHR.responseText,0);
				}
			});
		},

		saveField: function() {
			var self = this;
			var url = site_base_uri+"/admin/fields/save";
			var params = jQuery.param(self.cform.find('form.field-form').serializeArray());

			jQuery.ajax({
				url: url,
				data: params,
				dataType: 'json',
				method: 'POST',
				beforeSend: function() {
					self.loading();
				},
				success: function(json) {
					var error = parseInt(json.error);
					var message = json.message;
					self.removeLoading();
					if(error < 0) {
						switch(error) {
							case -1:
							case -2:
								self.abortDialog(message,0);
								break;
							default:
								self.abortDialog(message, ( error * -1 ) );
								break;
						}
					} else if(error > 0) {
						var fid = message.fid;
						var idx = message.idx;
						if(!parseInt(self.cform.find('input[name="fid"]').val())) {
							self.cform.find('input[name="fid"]').val(fid);
							self.cform.find('input[name="mode"]').val('modify');
							self.cform.find('input[name="idx"]').val(idx);
							self.cform.find('.field-form button.submit').text('수정');
							self.cform.find('button.delete').css({'display': 'inline-block'});
							self.cform.attr('data-index',idx-1);
							self.cform.attr('data-fid',fid);
							self.cform.attr('data-target',idx-1);
							self.cform.attr('id','field-form-item-'+fid);
							self.addItem(fid,message);
						} else {
							self.cform.find('input[name="idx"]').val(idx);
							self.rebuildItem(fid,message);
						}
					} else {
						self.abortDialog(message,0);
					}
				},
				error: function( jqXHR, textStatus, errorThrown ) {
					self.removeLoading();
					self.abortDialog(jqXHR.responseText,0);
				}
			});
		},

		addItem: function(fid,data) {
			var idx = data.idx-1;

			var $this = jQuery('<li class="field"><div class="inner"><label>'+data.subject+'</label></div></li>');
			var item = {};

			var addConsole = jQuery(this.settings.addconsole);
			addConsole.attr('target-index', idx );
			this.handleClick(addConsole,'add');
			addConsole.appendTo($this);

			var item = {};

			item.obj = $this;
			item.obj.attr('data-index',idx);
			item.fid = fid;
			item.obj.attr('data-fid',fid);
			item.idx = data.idx;
			item.obj.attr('data-idx',data.idx);
			item.subject = data.subject;
			item.iscolumn = parseInt(data.iscolumn);
			if(parseInt(data.iscolumn))
				item.obj.addClass('iscolumn');
			item.multiple = parseInt(data.multiple);
			if(parseInt(data.multiple))
				item.obj.addClass('multiple');
			item.required = parseInt(data.required);
			if(parseInt(data.required))
				item.obj.addClass('required');
			item.active = parseInt(data.active);
			if(parseInt(data.active))
				item.obj.addClass('active');
			item.autocomplete = parseInt(data.autocomplete);
			if(parseInt(data.autocomplete))
				item.obj.addClass('autocomplete');
			item.system = parseInt(data.system);
			if(parseInt(data.system))
				item.obj.addClass('system');
			item.type = data.type;
			item.obj.attr('data-type', data.type);
			item.cid = data.cid;
			item.obj.attr('data-cid', data.cid);
			item.indextype = data.indextype;
			item.obj.attr('data-indextype', data.indextype);

			this.handleClick(item.obj.find('.inner'),'modify');

			if( idx >= ( this.fields.length -1 ) ) {
				this.fields_index[fid] = this.fields.length;
				this.fields.push(item);
				this.listContainer.append(item.obj);
			} else {
				this.fields.insert(idx, item);
				item.obj.insertBefore( this.fields[idx+1].obj );
				this.fields_index = {};
				for(var i=0; i<this.fields.length; i++) {
					this.fields[i].obj.attr('data-index',i);
					this.fields[i].idx = (i+1);
					this.fields[i].obj.attr('data-idx', (i+1) );
					this.fields_index[this.fields[i].fid] = i;
					if(i == idx) {
						this.fields[i].obj.find('.inner').addClass('extended');
					} else {
						this.fields[i].obj.find('.inner').removeClass('extended');
						this.fields[i].obj.find('.field-console').removeClass('extended');
					}
				}
			}
		},

		rebuildItem: function(fid,data) {
			var idx = this.fields_index[fid];

			var new_idx = data.idx-1;
			if(idx != new_idx) {
				new_idx = this.sortItems(idx, new_idx);
			}
			var item = this.fields[new_idx];

			item.subject = data.subject
			if(item.subject != item.obj.find('label').text()) {
				item.obj.find('label').text(data.subject);
			}
			item.iscolumn = parseInt(data.iscolumn);
			if( parseInt(data.iscolumn) ) {
				item.obj.addClass('iscolumn');
			} else {
				item.obj.removeClass('iscolumn');
			}
			item.multiple = parseInt(data.multiple);
			if( parseInt(data.multiple) ) {
				item.obj.addClass('multiple');
			} else {
				item.obj.removeClass('multiple');
			}
			item.required = parseInt(data.required);
			if( parseInt(data.required) ) {
				item.obj.addClass('required');
			} else {
				item.obj.removeClass('required');
			}
			item.active = parseInt(data.active);
			if( parseInt(data.active) ) {
				item.obj.addClass('active');
			} else {
				item.obj.removeClass('active');
			}
			item.autocomplete = parseInt(data.autocomplete);
			if( parseInt(data.autocomplete) ) {
				item.obj.addClass('autocomplete');
			} else {
				item.obj.removeClass('autocomplete');
			}
			item.system = parseInt(data.system);
			if( parseInt(data.system) ) {
				item.obj.addClass('system');
			} else {
				item.obj.removeClass('system');
			}
			item.type = data.type;
			if(item.obj.attr('data-type') != data.type) {
				item.obj.attr( 'data-type', data.type );
			}
			item.cid = data.cid;
			if(item.obj.attr('data-cid') != data.cid) {
				item.obj.attr( 'data-cid', data.cid );
			}
			item.indextype = data.indextype;
			if(item.obj.attr('data-indextype') != data.indextype) {
				item.obj.attr( 'data-indextype', data.indextype );
			}
			this.fields[new_idx] = item;
		},

		sortItems: function(old_idx,new_idx) {
			var fields = [];
			if( !new_idx || new_idx >= this.fields.length )
				return this.fields.length;

			var nfields = this.fields[old_idx];
			for(var i=0; i < this.fields.length; i++) {
				if(i == old_idx)
					continue;
				else {
					if(i == new_idx)
						fields.push(nfields);
					fields.push(this.fields[i]);
				}
			}

			this.fields = [];
			this.fields = fields;
			this.fields_index = {};
			for(var i=0; i < this.fields.length; i++) {
				this.fields[i].obj.attr('data-index',i);
				this.fields[i].obj.find('.field-console').attr('target-index',i);
				this.fields[i].idx = (i+1);
				this.fields[i].obj.attr('data-idx', (i+1) );
				this.fields_index[this.fields[i].fid] = i;
				if(i == new_idx) {
					var cobj = this.fields[i].obj.clone();
					if( new_idx < ( this.fields.length - 1 ) ) {
						cobj.insertBefore(this.fields[i+1].obj);
					} else {
						cobj.insertAfter(this.fields[i-1].obj);
					}
					this.fields[i].obj.remove();
					this.fields[i].obj = cobj;
				}
			}

			return new_idx;
		},

		reIndex: function() {
			this.fields_index = {};
			for(var i=0; i<this.fields.length; i++) {
				this.fields[i].obj.attr('data-index',i);
				this.fields[i].obj.find('.field-console').attr('target-index',i);
				this.fields[i].idx = (i+1);
				this.fields[i].obj.attr('data-idx', (i+1) );
				this.fields_index[this.fields[i].fid] = i;
			}
		},

		deletes: function(fid) {
			var self = this;
			var url = site_base_uri+"/admin/fields/save";

			var params = "fid="+fid+"&mode=delete&table="+this.table;

			jQuery.ajax({
				url: url,
				data: params,
				dataType: 'json',
				method: 'POST',
				beforeSend: function() {
					self.loading();
				},
				success: function(json) {
					var error = parseInt(json.error);
					var message = json.message;
					self.removeLoading();
					if(error <= 0) {
						self.abortDialog(message,0);
					} else {
						self.cform.remove();
						self.deleteItem(fid);
					}
				},
				error: function( jqXHR, textStatus, errorThrown ) {
					self.removeLoading();
					self.abortDialog(jqXHR.responseText,0);
				}
			});
		},

		deleteItem: function(fid) {
			var idx = this.fields_index[fid];
			this.fields[idx].obj.remove();
			this.fields.remove(idx);
			this.fields_index = {};
			for(var i=0; i<this.fields.length; i++) {
				this.fields[i].obj.attr('data-index',i);
				this.fields[i].idx = (i+1);
				this.fields[i].obj.attr('data-idx', (i+1) );
				this.fields[i].obj.find('.field-console').attr('target-index',i);
				this.fields_index[this.fields[i].fid] = i;
			}
		},

		bindAddTaxonomy: function(element) {
			var self = this;
			element.find('button.taxonomy-add').click(function(e) {
				self.showAddTaxonomyForm(element);
			});

			element.find('.new-taxonomy-container button.taxonomy-add-submit').click(function(e) {
				self.addTaxonomy(jQuery(this).parent());
			});

			element.find('.new-taxonomy-container button.taxonomy-add-cancel').click(function(e) {
				self.hideAddTaxonomyForm(element);
			});
		},

		showAddTaxonomyForm: function(element) {
			var self = this;
			element.find('.new_taxonomy-button').css({'max-height': 0});
			var tip = element.find('.new-taxonomy-container');
			tip.css({ 'max-height' : '30px' });
			tip.bind(self.transitionEnd, function(e) {
				jQuery(this).find('input.text').focus();
				jQuery(this).unbind(self.transitionEnd);
			});
		},

		hideAddTaxonomyForm: function(element) {
			var tip = element.find('.new-taxonomy-container');
			tip.find('input.text').val('');
			tip.css({'max-height': 0});
			element.find('.new_taxonomy-button').css({'max-height': '30px'});
		},

		addTaxonomy: function(form) {
			var self = this;

			var ip = form.find('input.text');
			var subject = ip.val();
			if(!subject) {
				this.alertMessage(ip,'분류이름을 입력하세요');
				return;
			} else {
				this.removeAlert(ip);
			}
			var url = site_base_uri+'/admin/fields/taxonomy';
			var params = 'mode=add&subject=' + subject;

			jQuery.ajax({
				url: url,
				data: params,
				dataType: 'json',
				method: 'POST',
				beforeSend: function() {
					self.loading();
				},
				success: function(json) {
					self.removeLoading();
					var error = parseInt(json.error);
					var message = json.message;
					if(error < 0) {
						if(error == -2) {
							self.alertMessage(ip,message);
						} else {
							self.abortDialog(message,0);
							self.removeAlert(ip);
						}
					} else {
						self.removeAlert(ip);
						var select = self.cform.find('fieldset.field-item.taxonomy select#cid');
						var options = jQuery('<option value="' + message.cid + '">' + message.subject + '</option>');
						options.appendTo(select);
						self.hideAddTaxonomyForm(self.cform.find('.new-taxonomy-wrapper'));
					}
				},
				error: function( jqXHR, textStatus, errorThrown ) {
					self.removeLoading();
					self.abortDialog(jqXHR.responseText,0);
				}
			});
		},

		showTaxonomyTerms: function(fid,cid) {
			var self = this;
			var f = this.tform.clone();

			var url = site_base_uri+'/api/taxonomy';
			var params = 'cid='+cid+"&type=1";

			jQuery.ajax({
				url: url,
				data: params,
				dataType: 'json',
				method: 'GET',
				beforeSend: function() {
					self.loading();
				},
				success: function(json) {
					self.removeLoading();
					if(json.error) {
						self.abortDialog(error,0);
					} else {
						self.initTaxonomyForm(f,fid,cid);
						self.buildTaxonomyTerms(f,json,cid);

						var l = jQuery('#field-form-item-'+fid).position().left + jQuery('#field-form-item-'+fid).outerWidth() + 10;
						f.css({
							'top': 0,
							'left': l+'px',
							'max-height': ( self.Root.innerHeight() )+'px'
						});
						f.find('.taxonomy-terms-wrapper').css({
							'max-height': ( self.Root.innerHeight() - 40 )+'px'
						});
						self.Root.append(f);
					}
				},
				complete: function() {
					setTimeout(function() {
						f.removeClass('collapsed');
					}, 10);
				},
				error: function( jqXHR, textStatus, errorThrown ) {
					self.removeLoading();
					self.abortDialog(jqXHR.responseText,0);
				}
			});
		},

		initTaxonomyForm: function(element,fid,cid) {
			var self = this;
			element.attr('data-fid',fid);
			element.attr('data-cid',cid);
			element.attr('id','taxonomy-terms-'+cid);
			element.find('i.fa-close, button.close').click(function(e) {
				e.preventDefault();
				self.removeTaxonomyTerms(element);
			});
		},

		buildTaxonomyTerms: function(element,json,cid) {
			var self = this;
			var ls = element.find('ol');

//			jQuery.each(json.taxonomy[cid], function (tid, data) {
			jQuery.each(json.taxonomy, function (i, data) {
				var tli = self.TaxonomyTermObj( data.tid, data.cid, data.vid, data.parent, data.idx, data.nsubs, data.name );
				self.bindModifyTaxonomyTerms(tli.find('i.modify'));
				tli.find('i.delete').click(function(e) {
					self.deleteTaxonomyTerms(jQuery(this).parents('li.taxonomy-term'));
				});

				var cs = self.TaxonomyTermConsole(data.idx);
				cs.find('button').click(function(e) {
					e.preventDefault();
					self.addTaxonomyTerms(jQuery(this).parents('li.taxonomy-term'));
				});
				cs.appendTo(tli);
				ls.append(tli);
			});
		},

		TaxonomyTermObj(tid,cid,vid,parents,idx,nsubs,name) {
			var obj = jQuery(this.settings.template.terms);
			if(parseInt(parents) > 0)
				obj.addClass('sub');
			obj.attr( 'id', 'taxonomy_term_' + tid );
			obj.attr( 'data-cid', cid );
			obj.attr( 'data-tid', tid );
			obj.attr( 'data-vid', vid );
			obj.attr( 'data-parent', parents );
			obj.attr( 'data-idx', idx );
			obj.attr( 'data-nsubs', nsubs );
			obj.find('label').text(name);

			return obj;
		},

		TaxonomyTermConsole: function(idx) {
			var obj = jQuery(this.settings.template.addtermconsole);
			obj.attr('target-index', idx);

			return obj;
		},

		addTaxonomyTerms: function(element) {
			var tli = this.TaxonomyTermObj( 0, element.attr('data-cid'), 0, element.attr('data-parent'), ( parseInt(element.attr('data-idx')) + 1 ), 0, '' );
			tli.addClass('new-taxonomy');

			var editor = tli.find('label');
			var mbutton = tli.find('i.modify');
			var dbutton = tli.find('i.delete');

			tli.insertAfter(element);
			editor.attr('contenteditable',true).focus();
			mbutton.addClass('active');
			dbutton.bind('click.cancel_taxonomy', function(e) {
				$this = jQuery(this);
				$this.parents('li.taxonomy-term').remove();
			});

			this.bindAddTaxonomyTerms(mbutton);
		},

		bindAddTaxonomyTerms: function(element) {
			var self = this;
			element.bind('click.add_taxonomy', function(e) {
				$this = jQuery(this);
				self.insertTaxonomyTerms( $this.parents('li.taxonomy-term') );
			});
		},

		insertTaxonomyTerms: function(element) {
			var self = this;

			var label = element.find('label');
			var mbutton = element.find('i.modify');

			var name = label.text();
			if(!name) {
				this.alertMessage(label, '분류항목이름을 입력하세요');
				return;
			} else {
				this.removeAlert(label);
			}
			var url = site_base_uri+'/admin/fields/terms';
			var params = "mode=add&cid=" + element.attr('data-cid') + "&parent=" + element.attr('data-parent') + "&idx=" + element.attr('data-idx') + "&name=" + name;
			console.log(params);
			jQuery.ajax({
				url: url,
				data: params,
				dataType: 'json',
				method: 'POST',
				beforeSend: function() {
					self.loading();
				},
				success: function(json) {
					var error = parseInt(json.error);
					var message = json.message;
					self.removeLoading();
					if(error <= 0) {
						if(error == -3) {
							self.alertMessage(label, message);
						} else {
							self.abortDialog(message,0);
							self.removeAlert(label);
						}
					} else {
						self.removeAlert(label);
						label.attr('contenteditable',false);
						mbutton.removeClass('active');
						element.removeClass('new-taxonomy');
						element.attr('data-editing',0);
						element.attr( 'id', 'taxonomy_term_' + message.tid );
						element.attr( 'data-tid', message.tid );
						element.attr( 'data-vid', message.vid );
						var cs = self.TaxonomyTermConsole(message.idx);
						cs.find('button').click(function(e) {
							e.preventDefault();
							self.addTaxonomyTerms(jQuery(this).parents('li.taxonomy-term'));
						});
						cs.appendTo(element);

						mbutton.unbind('click.add_taxonomy');
						self.bindModifyTaxonomyTerms(mbutton);
						element.find('i.delete').unbind('click.cancel_taxonomy');
						element.find('i.delete').click(function(e) {
							self.deleteTaxonomyTerms(jQuery(this).parents('li.taxonomy-term'));
						});
						self.resortTaxonomyTerms(message.cid, message.parent);
					}
				},
				error: function( jqXHR, textStatus, errorThrown ) {
					self.removeLoading();
					self.abortDialog(jqXHR.responseText,0);
				}
			});
		},

		bindModifyTaxonomyTerms: function(element) {
			var self = this;
			element.bind('click',function(e) {
				$this = jQuery(this);
				if(parseInt( $this.parents('li.taxonomy-term').attr('data-editing') ) !== 1) {
					self.modifyTaxonomyTerms( $this.parents('li.taxonomy-term') );
				} else {
					self.updateTaxonomyTerms( $this.parents('li.taxonomy-term') );
				}
			});
		},

		modifyTaxonomyTerms: function(element) {
			var self = this;
			var editor = element.find('label');
			var mbutton = element.find('i.modify');

			element.attr('data-orgin',editor.text());
			element.attr('data-editing', 1);

			editor.attr('contenteditable',true).focus();

//			editor.focusout(function(event) {
//				var $this = jQuery(this);
//				self.updateTaxonomyTerms( $this.parent() );
//			});

			mbutton.addClass('active');
		},

		updateTaxonomyTerms: function( element ) {
			var self = this;

			var label = element.find('label');
			var mbutton = element.find('i.modify');

/*			label.attr('contenteditable',false);
			mbutton.removeClass('active');
			element.attr('data-editing',0); */

			var origin = element.attr('data-orgin');
			var name = label.text();
			if(!name) {
				this.alertMessage(label,'분류항목이름을 입력하세요');
				return;
			} else {
				this.removeAlert(label);
			}
			if( origin == name ) {
				label.attr('contenteditable',false);
				mbutton.removeClass('active');
				element.attr('data-editing',0);
				return;
			}
			var url = site_base_uri+'/admin/fields/terms';
			var params = "mode=modify&cid=" + element.attr('data-cid') + "&tid=" + element.attr('data-tid') + "&vid=" + element.attr('data-vid') + "&parent=" + element.attr('data-parent') + "&idx=" + element.attr('data-idx') + "&name=" + name;
			jQuery.ajax({
				url: url,
				data: params,
				dataType: 'json',
				method: 'POST',
				beforeSend: function() {
					self.loading();
				},
				success: function(json) {
					var error = parseInt(json.error);
					var message = json.message;
					self.removeLoading();
					if(error <= 0) {
						if(error == -3) {
							self.alertMessage(label, message);
						} else {
							self.abortDialog(message,0);
							self.removeAlert(label);
						}
					} else {
						label.attr('contenteditable',false);
						mbutton.removeClass('active');
						element.attr('data-editing',0);
					}
				},
				error: function( jqXHR, textStatus, errorThrown ) {
					self.removeLoading();
					self.abortDialog(jqXHR.responseText,0);
				}
			});
		},

		deleteTaxonomyTerms: function(element) {
			var self = this;

			var url = site_base_uri+'/admin/fields/terms';
			var params = "mode=delete&cid=" + element.attr('data-cid') + "&tid=" + element.attr('data-tid') + "&vid=" + element.attr('data-vid') + "&parent=" + element.attr('data-parent') + "&idx=" + element.attr('data-idx');
			console.log(params);
			jQuery.ajax({
				url: url,
				data: params,
				dataType: 'json',
				method: 'POST',
				beforeSend: function() {
					self.loading();
				},
				success: function(json) {
					var error = parseInt(json.error);
					var message = json.message;
					self.removeLoading();
					if(error <= 0) {
						self.abortDialog(message,0);
					} else {
						var cid = element.attr('data-cid');
						var parents = element.attr('data-parent');

						element.remove();

						self.resortTaxonomyTerms(cid, parents);
					}
				},
				error: function( jqXHR, textStatus, errorThrown ) {
					self.removeLoading();
					self.abortDialog(jqXHR.responseText,0);
				}
			});
		},

		resortTaxonomyTerms: function(cid,parents) {
			var container = jQuery('#taxonomy-terms-'+cid+' .taxonomy-terms-list ol');
			var nsubs = 0;
			container.find('li[data-parent="'+parents+'"]').each(function(i) {
				$this = jQuery(this);
				$this.attr('data-idx', (i+1) )
				nsubs++;
			});
			if(parents) {
				container.find('li[data-tid="' + parents + '"]').attr('data-nsubs',nsubs);
			}
		},

		removeTaxonomyTermsByCid: function(cid) {
			var $id = 'taxonomy-terms-'+cid;
			var f = this.Root.find('#'+$id);
			this.removeTaxonomyTerms(f);
		},

		removeTaxonomyTerms: function(f) {
			var self = this;
			f.addClass('collapsed');
			var fid = 'field-form-item-'+f.attr('data-fid');
			f.find('.taxonomy-terms-list').bind(self.transitionEnd, function(e) {
				f.remove();
			});
			jQuery('#'+fid+' button.taxonomy-terms').removeClass('extended');
		},

		abortDialog: function(message,fid) {
			var self = this;
			this.focus_id = fid;
			if(!this.dialog) {
				this.dialog = jQuery(this.settings.dialog);
				this.dialog.appendTo('body');
			} else {
				this.dialog.show();
			}
			this.mode = 'dialog';
			this.dialog.find('.alert-dialog-inner').html(message);
			var w = this.dialog.outerWidth();
			var h = this.dialog.outerHeight();
			this.dialog.css({
				'left' : parseInt( ( jQuery(window).width() - w ) / 2 )+'px',
				'top' : parseInt( ( jQuery(window).height() - h ) / 2 )+'px'
			});
			var d = this.dialog.find('.close');
			if(d.attr('init-bind') != true) {
				d.bind('click',function(e) {
					self.closeDialog();
				});
				d.attr('init-bind',true);
			}
		},

		closeDialog: function() {
			this.dialog.hide();
			if(this.focus_id) {
				jQuery('.cadb-field-column[tabindex="'+Math.abs(this.focus_id)+'"]').focus();
				this.focus_id = 0;
			}
			this.mode = 'dialog';
		},

		alertMessage: function(element,message) {
			element.focus();
			element.addClass('cadb-input-alert');
			var alertID = 'cadb-alert-'+element.attr('data-alert');
			var contain = element.parent();
			contain.addClass('cadb-field-alert');
			var alerts = jQuery('span#'+alertID);
			if(!alerts.length) {
				var ui = this.uniqueID(8);
				var alerts = jQuery('<span id="cadb-alert-' + ui + '" class="cadb-message-alert">'+message+'</span>');
				alerts.appendTo('body');
				var l = element.offset().left;
				var t = element.offset().top + element.outerHeight() + 8;
				alerts.css({
					'left' : l+'px',
					'top' : t+'px'
				});
				element.attr('data-alert',ui);
			} else {
				alerts.text(message);
			}
		},

		uniqueID: function(size) {
			var getRandomNumber = function(range) {
				return Math.floor(Math.random() * range);
			};

			var getRandomChar = function() {
				var chars = "abcdefghijklmnopqurstuvwxyzABCDEFGHIJKLMNOPQURSTUVWXYZ";
				return chars.substr( getRandomNumber(62), 1 );
			};

			var randomID = function(size) {
				var str = "";
				for(var i = 0; i < size; i++) {
					str += getRandomChar();
				}
				return str;
			};

			return randomID(size);
		},

		removeAlert: function(element) {
			var contain = element.parent();
			var alertID = 'cadb-alert-'+element.attr('data-alert');
			element.removeClass('cadb-input-alert');
			contain.removeClass('cadb-field-alert');
			jQuery('span#'+alertID).remove();
		},

		loading: function() {
			jQuery('body').append(jQuery('<div class="saving"><div class="saving-background"></div><div class="is-loading"><i class="fa fa-spinner fa-pulse"></i></div></div>'));
			jQuery('.saving .is-loading').css({
				'left' : parseInt( ( jQuery(window).width() - 100 ) / 2 ),
				'top' : parseInt( ( jQuery(window).height() - 100 ) / 2 )
			});
		},

		removeLoading: function() {
			jQuery('body .saving').remove();
		}
	}

	jQuery.fn.cadbFieldEditor = function(options) {
		return this.each(function() {
			var cadbFieldEditor = new CADBFieldEditor(jQuery(this),options);
		});
	};

	jQuery.fn.cadbFieldEditor.defaults = {
		searchBoxBackground : '<div class="searching-org-list"></div>',
		searchBox : '<div class="search-list-box"><div class="search-inner-box"><h3>조직 검색</h3><form action="/api/orgs" method="POST"><input type="text" name="q" value=""><button type="submit">검색</button></form><section class="orgs"></section><div class="progress-spinner><i class="fa fa-spinner"></i></div></div><div class="close"><i class="fa fa-close"></i></div></div>',
		dialog : '<div class="alert-dialog"><div class="alert-dialog-inner"></div><div class="close"><i class="fa fa-close"></i></div></div>',
		template: {
			addfieldconsole : '<div class="field-console"><button type="button" class="field-add">필드추가</button></div>',
			terms : '<li class="taxonomy-term"><div class="inner"><label></label><i class="modify fa fa-pencil-square-o"></i><i class="delete fa fa-minus-square-o"></i></div>',
			addtermconsole : '<div class="terms-console"><button type="button" class="terms-add">분류항목추가</button></div>'
		}
	};
	
	jQuery.fn.cadbFieldEditor.settings = {
	};
})(jQuery);

jQuery(document).ready(function(e) {
	jQuery('.field-form-edit').cadbFieldEditor({
	});
});
