import React from 'react';
import PageItem from './PageItem';

const PageList = ({
  pages
}) => {
	const items = pages.map(makeItem);
	const rows = items.length ? <ul> {items} </ul> : <ul></ul>;

	return (
		<div className="page-navi-box">
		  {rows}
		</div>
	);
};

export default PageList;

function makeItem(page) {
	const { type, value } = page;
	const props = {
		type, value,
	};
	return <PageItem key={value} {...props} />
}
