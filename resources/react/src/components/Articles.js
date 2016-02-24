import React from 'react';
import Article from './Article';

const Articles = ({
  articles,
}) => {
  const rows = articles.map((article) => {
    return (
      <Article key={article.agreementId} {...article} />
    );
  });
  return (
    <div className="articles">
      <h3>조문 검색 결과</h3>
      {rows}
    </div>
  );
};

export default Articles;
