import React from 'react';
import { Link } from 'react-router';

const PageItem = ({
	type, value
}) => {
  const pclass = `${type}`;
  const pathname = window.location.pathname;
  const query = window.location.search.replace(/&page=[0-9]+/,"");
  const uri = `${pathname}${query}`;

  return (
    <li key={value} className={pclass}>
		<Link to={`${uri}&page=${value}`}><span>{value}</span></Link>
	</li>
  );
};

export default PageItem;
