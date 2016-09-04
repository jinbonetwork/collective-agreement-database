import React from 'react';
import ArticleItem from './ArticleItem';
import { Link } from 'react-router';

const ArticleList = ({
  result, articles
}) => {
  const items = articles.map(makeItem);
  const rows = items.length ? <ul> {items} </ul>
             : <div className="no-result">검색 결과가 없습니다.</div>;
  const more = result.articles && result.articles.more || 0;
  const moreClass = ( more ? 'more-box show' : 'more-box hide');
  const basename = site_base_uri;
  const query = window.location.search;
  const totalCount = result.articles && result.articles.total_cnt || 0;

  return (
    <div className="article-result">
      <div className="header">조문 검색 결과 :: {' '}
        <span className="result-counts">{totalCount}</span>개
      </div>
      {rows}
	  <div className={moreClass}><Link to={`${basename}/articles${query}`}><span>더보기</span></Link></div>
    </div>
  );
};

export default ArticleList;

function makeItem(article) {
  const { nid, subject, content } = article;

  const props = {
    article:  article,
  };
  return <ArticleItem key={`article-${nid}`} {...props} />;
}
