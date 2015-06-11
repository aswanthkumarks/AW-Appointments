
var edButtons = new Array();

var extendedStart = edButtons.length;

// below here are the extended buttons
edButtons[edButtons.length] =
new edButton('ed_strong'
,'b'
,'<strong>'
,'</strong>'
,'b'
);

edButtons[edButtons.length] =
new edButton('ed_em'
,'i'
,'<em>'
,'</em>'
,'i'
);

edButtons[edButtons.length] =
new edButton('ed_link'
,'link'
,''
,'</a>'
,'a'
); // special case

edButtons[edButtons.length] =
new edButton('ed_block'
,'b-quote'
,'<blockquote>'
,'</blockquote>'
,'q'
);

edButtons[edButtons.length] =
new edButton('ed_pre'
,'code'
,'`'
,'`'
,'c'
);

edButtons.push(
	new edButton(
		'ed_ol'
		,'ol'
		,'<ol>\n'
		,'</ol>\n\n'
		,'o'
	)
);

edButtons.push(
	new edButton(
		'ed_ul'
		,'ul'
		,'<ul>\n'
		,'</ul>\n\n'
		,'u'
	)
);

edButtons.push(
	new edButton(
		'ed_li'
		,'li'
		,'\t<li>'
		,'</li>\n'
		,'l'
	)
);
