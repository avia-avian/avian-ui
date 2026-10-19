/*
 * Builds the editor bundle and its add-ons into public/js:
 *
 *   avian-ui-editor.js        Tiptap, ProseMirror and the extensions every editor may use
 *   avian-ui-editor-code.js   syntax highlighting (lowlight)
 *   avian-ui-editor-emoji.js  emoji data and the emoji node
 *
 * The add-ons take @tiptap/core and @tiptap/pm/* from the editor bundle
 * (window.AvianUITiptapModules): two ProseMirror copies on one page break.
 */
import { build } from 'esbuild';

const shared = ['@tiptap/core', '@tiptap/pm/model', '@tiptap/pm/state', '@tiptap/pm/view'];

const options = {
    bundle: true,
    minify: true,
    format: 'iife',
    target: 'es2019',
    legalComments: 'eof',
    logLevel: 'info',
};

const fromEditorBundle = {
    name: 'avian-ui-editor-bundle',
    setup(build) {
        build.onResolve({ filter: /^@tiptap\/(core|pm\/.*)$/ }, (args) => {
            if (! shared.includes(args.path)) {
                throw new Error(args.path + ' is not shared by the editor bundle; add it to `shared` and to resources/js/editor/index.js.');
            }

            return { path: args.path, namespace: 'avian-ui-editor-bundle' };
        });

        build.onLoad({ filter: /.*/, namespace: 'avian-ui-editor-bundle' }, (args) => ({
            contents: `var modules = window.AvianUITiptapModules;
if (! modules) { throw new Error('[Avian UI] Load avian-ui-editor.js before its add-ons.'); }
module.exports = modules[${JSON.stringify(args.path)}];`,
        }));
    },
};

await build({
    ...options,
    entryPoints: ['resources/js/editor/index.js'],
    outfile: 'public/js/avian-ui-editor.js',
    banner: { js: '/*! Avian UI editor bundle: Tiptap (MIT, überdosis GbR), ProseMirror (MIT, Marijn Haverbeke and others), linkifyjs (MIT, Hypercontext). Built from resources/js/editor/index.js. */' },
});

await build({
    ...options,
    entryPoints: ['resources/js/editor/code.js'],
    outfile: 'public/js/avian-ui-editor-code.js',
    plugins: [fromEditorBundle],
    banner: { js: '/*! Avian UI editor add-on: code highlighting. Tiptap (MIT, überdosis GbR), lowlight (MIT, Titus Wormer), highlight.js (BSD-3-Clause, Ivan Sagalaev and others). Built from resources/js/editor/code.js. */' },
});

await build({
    ...options,
    entryPoints: ['resources/js/editor/emoji.js'],
    outfile: 'public/js/avian-ui-editor-emoji.js',
    plugins: [fromEditorBundle],
    banner: { js: '/*! Avian UI editor add-on: emoji. Tiptap (MIT, überdosis GbR), emoji data from GitHub gemoji (MIT) and emojibase (MIT, Miles Johnson). Built from resources/js/editor/emoji.js. */' },
});
