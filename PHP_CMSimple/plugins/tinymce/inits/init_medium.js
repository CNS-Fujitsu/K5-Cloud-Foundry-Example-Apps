var tinyConfig = {
    selector: "cmsimple-editor",
    skin: "cmsimple",
    block_formats: "h1 page=h1;h2 page=h2;h3 page=h3;Div=div;Paragraph=p;Header 4=h4;Header 5=h5;Header 6=h6;code=code;pre=pre",
    toolbar_items_size: "small",
    fontsize_formats: "8px 10px 12px 14px 15px 16px 18px 20px 24px 26px 30px 36px",
    entity_encoding : "named",
    entities : "160,nbsp",
    element_format : "html",
    extended_valid_elements: "script[type|language|src]",
    image_title: true,
    autosave_ask_before_unload: true,
    plugins: [
        "autosave advlist autolink lists link image media charmap print preview hr anchor pagebreak",
        "searchreplace wordcount visualblocks visualchars code fullscreen nonbreaking",
        "insertdatetime media nonbreaking save table contextmenu directionality",
        "emoticons template paste textcolor colorpicker textpattern importcss"
    ],
    image_dimensions: false,
    image_advtab: true,
    importcss_append: true,
    importcss_groups: [
        {title: "Table styles", filter: /^(td|tr|table)\./},
        {title: "Block styles", filter: /^(div|p|ul|ol|h1|h2|h3|h4|h5|h6)\./},
        {title: "Image styles", filter: /^(img)\./},
        {title: "Other styles"}
    ],
    style_formats: [
		{title: "New Page", items: [
			{title: "h1 page", format: "h1"},
			{title: "h2 page", format: "h2"},
			{title: "h3 page", format: "h3"}
		]},
		{title: "Headers", items: [
			{title: "Header 4", format: "h4"},
			{title: "Header 5", format: "h5"},
			{title: "Header 6", format: "h6"}
		]},
		{title: "Inline", items: [
			{title: "Bold", icon: "bold", format: "bold"},
			{title: "Italic", icon: "italic", format: "italic"},
			{title: "Underline", icon: "underline", format: "underline"},
			{title: "Strikethrough", icon: "strikethrough", format: "strikethrough"},
			{title: "Superscript", icon: "superscript", format: "superscript"},
			{title: "Subscript", icon: "subscript", format: "subscript"},
			{title: "Code", icon: "code", format: "code"}
		]},
		{title: "Blocks", items: [
			{title: "Paragraph", format: "p"},
			{title: "Div", format: "div"},
			{title: "Blockquote", format: "blockquote"},
			{title: "Pre", format: "pre"}
		]},
		{title: "Alignment", items: [
			{title: "Left", icon: "alignleft", format: "alignleft"},
			{title: "Center", icon: "aligncenter", format: "aligncenter"},
			{title: "Right", icon: "alignright", format: "alignright"},
			{title: "Justify", icon: "alignjustify", format: "alignjustify"}
		]}
    ],
    menu: {
        edit: {title: "Edit", items: "cut copy paste pastetext searchreplace | undo redo | selectall"},
        insert: {title: "Insert", items: "image link charmap hr nonbreaking"},
        view: {title: "View", items: "visualaid visualblocks visualchars | fullscreen code"},
        format: {title: "Format", items: "formats | bold italic underline strikethrough superscript subscript | removeformat"},
        table: {title: "Table", items: "inserttable tableprops deletetable | cell row column"},
        tools: {title: "Tools", items: "spellchecker code"}
    },
    menubar: "edit format insert view table",
    toolbar: "save code fullscreen | undo redo"
};