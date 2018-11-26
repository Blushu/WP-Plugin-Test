( function($) {

	$(document).ready( function() {

		// code plugin
		tinymce.PluginManager.add("code", function(a) {
			function b() {
				var b = a.windowManager.open({
					title: "Source code",
					body: {
						type: "textbox",
						name: "code",
						multiline: !0,
						minWidth: a.getParam("code_dialog_width", 600),
						minHeight: a.getParam("code_dialog_height", Math.min(tinymce.DOM.getViewPort().h - 200, 500)),
						spellcheck: !1,
						style: "direction: ltr; text-align: left"
					},
					onSubmit: function(b) {
						a.focus(), a.undoManager.transact(function() {
							a.setContent(b.data.code)
						}), a.selection.setCursorLocation(), a.nodeChanged()
					}
				});
				b.find("#code").value(a.getContent({
					source_view: !0
				}))
			}
			a.addCommand("mceCodeEditor", b), a.addButton("code", {
				icon: "code",
				tooltip: "Source code",
				onclick: b
			}), a.addMenuItem("code", {
				icon: "code",
				text: "Source code",
				context: "tools",
				onclick: b
			})
		});

		var plugin_options = "lists link charmap code";
					
		var toolbar_options = "undo redo | formatselect | bold italic strikethrough charmap | gform | link | alignleft aligncenter alignright alignjustify  | numlist bullist outdent indent  | removeformat | code";

		var ed = new tinymce.Editor(to_convert, {
			theme: 'modern',
			convert_urls: 0,
			remove_script_host: 0,
			branding: false,
			menubar: false,
			verify_html: false,
			toolbar: toolbar_options,
			plugins: plugin_options,
			anchor_top: false,
			anchor_bottom: false,
			link_context_toolbar: true,
			// link_list: blitz.link_list,
			link_title: false,
			setup: function (editor) {
				editor.on( 'onPostRender', function (e) {
					console.log('test');
					$('label:contains("Link list")').text('Pages');
				});
			}
		}, tinymce.EditorManager);

		ed.render();

	});

})(jQuery);