import React from 'react';
import { Link } from 'react-router';

const Org = ({
  name,
  orgId,
  agreementId,
}) => {
  return (
    <div className="org">
      {name} /
      <Link to={`/org/${orgId}`}> {orgId} </Link> /
      <Link to={`/agreement/${agreementId}`}> {agreementId} </Link>
    </div>
  );
};

export default Org;
