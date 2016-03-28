import React from 'react';
import { Link } from 'react-router';

const StandardItem = ({
  sid, subject, content,
}) => {
  return (
    <li>
      <div className="header">{subject}</div>
      <div className="content"
        dangerouslySetInnerHTML={{ __html: content }} />
      <div className="footer">
        <Link to={`/standards/${sid}`}>
          <button type="button" name="read-article-comment">조문해설 보기</button>
        </Link>
      </div>
    </li>
  );
};

export default StandardItem;
