import React from 'react';
import { Link } from 'react-router';

const StandardIndexes = ({
  indexes, onIndexClick
}) => {
  const indexRows = indexes.map(({ id, subject, nsubs, articles }) => {
    const did = `guide-chapter-${id}`;
    const k = `guide-chapter-title-${id}`;
	const items = articles.map(makeItem);
	const rows = items.length ? <dd className="chapter-articles"> {items} </dd> : '';

    if(nsubs) {
      return (
	    <dl id={did} key={did} className="collapsed">
          <dt key={k} className="chapter-title" onClick={() => onIndexClick(id,nsubs)}>
            <span>{subject}</span>
          </dt>
          {rows}
        </dl>
	  );
	} else {
      return (
        <dl id={did} key={did} className="collapsed">
          <dt key={k} className="chapter-title">
            <Link to={`/standards/${id}`}><span>{subject}</span></Link>
          </dt>
        </dl>
	  );
	}
  });
  return (
    <div className="guideIndexes">
      {indexRows}
	</div>
  );
};

export default StandardIndexes;

function makeItem(article) {
  const { id, subject } = article;
  const k=`guide-article-${id}`;
  const sid = window.location.pathname.split("/").splice(-1)[0];

  return <article key={k} id={k} className="article"><Link to={`/standards/${id}`}><span>{subject}</span></Link></article>;
}
