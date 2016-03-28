import React from 'react';
import ArticleItem from './ArticleItem';

const ArticleList = ({
  result, articles
}) => {
  const items = articles.map(makeItem);
  const rows = items.length ? <ul> {items} </ul>
             : <div className="no-result">검색 결과가 없습니다.</div>;
  const totalCount = result.articles && result.articles.total_cnt || 0;

  return (
    <div className="article-result">
      <div className="header">조문 검색 결과 :: {' '}
        <span className="result-counts">{totalCount}</span>개
      </div>
      {rows}
    </div>
  );
};

export default ArticleList;

function makeItem(article) {
  const { nid, subject, content } = article;
  const cat1 = article.f28 && article.f28[0].name;
  const cat2 = article.f28 && article.f28[1].name;
  const ndate = article.f31;
  const term = article.f32;

  const props = {
    nid, subject, content,
    cat1, cat2, ndate, term,
  };
  return <ArticleItem key={nid} {...props} />;
}
