import React from 'react';
import { Link } from 'react-router';

const ArticleItem = ({
  nid, subject, content, cat1, cat2, ndate, term,
}) => {
  return (
    <li key={nid}>
      <div className="header">
        <div className="title">
          <div className="organ-name"><Link to={`/articles/${nid}`}>{subject}</Link></div>
          <Link to={`/articles/${nid}`} className="view-whole">전문 보기</Link>
        </div>
        <div className="info">
          <span className="bargain-cat-1">{cat1}</span>,&nbsp;
          <span className="bargain-cat-2">{cat2}</span>,&nbsp;
          협약체결일_<span className="agree-date">{ndate}</span>,&nbsp;
          유효기간_<span className="validity-term">{term}</span>년
        </div>
      </div>
      <div className="content">
        <p>{content}</p>
      </div>
    </li>
  );
};

export default ArticleItem;
