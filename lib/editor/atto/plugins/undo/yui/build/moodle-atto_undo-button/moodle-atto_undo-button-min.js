YUI.add("moodle-atto_undo-button",function(e,t){var n="moodle-atto_undo-button";e.namespace("M.atto_undo").Button=e.Base.create("button",e.M.editor_atto.EditorPlugin,[],{_maxUndos:40,_undoStack:null,_redoStack:null,initializer:function(){this._undoStack=[],this._redoStack=[],this.addButton({icon:"e/undo",callback:this._undoHandler,buttonName:"undo",keys:90}),this.addButton({icon:"e/redo",callback:this._redoHandler,buttonName:"redo",keys:89}),this.get("host").on("pluginsloaded",function(){this._addToUndo(this._getHTML()),this.get("host").on("atto:selectionchanged",this._changeListener,this)},this),this._updateButtonsStates()},_addToRedo:function(e){this._redoStack.push(e)},_addToUndo:function(e,t){var n=this._undoStack[this._undoStack.length-1];typeof t=="undefined"&&(t=!1),n!==e&&(this._undoStack.push(e),t&&(this._redoStack=[]));while(this._undoStack.length>this._maxUndos)this._undoStack.shift()},_getHTML:function(){return this.get("host").getCleanHTML()},_getRedo:function(){return this._redoStack.pop()},_getUndo:function(e){return this._undoStack.length===1?this._undoStack[0]:(last=this._undoStack.pop(),last===e&&(last=this._undoStack.pop()),this._undoStack.length===0&&this._addToUndo(last),last)},_restoreValue:function(e){this.editor.setHTML(e),this._addToUndo(e)},_updateButtonsStates:function(){this._undoStack.length>1?this.enableButtons("undo"):this.disableButtons("undo"),this._redoStack.length>0?this.enableButtons("redo"):this.disableButtons("redo")},_undoHandler:function(e){e.preventDefault();var t=this._getHTML(),n=this._getUndo(t);if(t===n){this._updateButtonsStates();return}this._restoreValue(n),this._addToRedo(t),this._updateButtonsStates()},_redoHandler:function(e){e.preventDefault();var t=this._getHTML(),n=this._getRedo();if(t===n){this._updateButtonsStates();return}this._restoreValue(n),this._updateButtonsStates()},_changeListener:function(e){if(e.event.type.indexOf("key")!==-1&&e.event.keyCode!==39&&e.event.keyCode!==37&&e.event.keyCode!==40&&e.event.keyCode!==38)return;this._addToUndo(this._getHTML(),!0),this._updateButtonsStates()}})},"@VERSION@",{requires:["moodle-editor_atto-plugin"]});