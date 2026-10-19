/*
 * Avian UI - syntax highlighting add-on for <x-avian::editor>
 *
 * Load it after the editor bundle with <x-avian::scripts editor="code" />.
 * It shares the editor bundle's Tiptap and ProseMirror, so it never runs on
 * its own. Highlights the common languages lowlight ships with.
 */
import CodeBlockLowlight from '@tiptap/extension-code-block-lowlight';
import { createLowlight, common } from 'lowlight';

window.AvianUITiptap = Object.assign({}, window.AvianUITiptap, {
    CodeBlockLowlight,
    lowlight: createLowlight(common),
});
