import React from 'react';
import { Link } from 'react-router';

const Article = ({
  name,
  agreementId,
}) => {
  return (
    <div className="article">
      {name} /
      <Link to={`/agreement/${agreementId}`}> {agreementId} </Link>
    </div>
  );
};

export default Article;
