do ($ = jQuery, window) ->
	$ ->
		$textArea = $ '#template-editor'
		if $textArea.length
			editor = CodeMirror.fromTextArea($textArea.get(0), lineNumbers: true, mode: 'xml')


