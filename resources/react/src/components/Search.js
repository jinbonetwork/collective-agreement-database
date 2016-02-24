import React from 'react';
import { Link } from 'react-router';

export default class Search extends React.Component {
  render() {
    const keyword = this.props.location.query.q;
    return (
      <div>
        <ul>
          <li> 모범 단협 : {keyword} </li>
          <li> <Link to={`/search/orgs/${keyword}`}>
            /search/orgs/{keyword}</Link> </li>
          <li> <Link to={`/search/articles/${keyword}`}>
            /search/articles/{keyword}</Link> </li>
        </ul>
      </div>
    );
  }
}
