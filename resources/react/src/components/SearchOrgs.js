import React from 'react';
import Orgs from './Orgs';
import axios from 'axios';

export default class SearchOrgs extends React.Component {
  constructor() {
    super();
    this.state = {
      orgs: [],
    };
  }
  render() {
    return (
      <Orgs orgs={this.state.orgs} />
    );
  }
  componentDidMount() {
    this.doSearch(this.props.params.keyword);
  }
  doSearch(keyword) {
    const url = '/api/org?q='+keyword;
    axios.get(url)
    .then((res) => {
      console.log('- searchArticles', keyword);
      this.setState({ orgs: res.data.orgs });
    });
  }
}
