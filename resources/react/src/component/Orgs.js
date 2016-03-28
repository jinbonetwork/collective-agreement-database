import React, { Component } from 'react';
import axios from 'axios';
import OrgList from './Org/OrgList';

export default class Orgs extends Component {
  constructor() {
    super();
    this.state = {
      orgs: [],
    };
  }

  render() {
    return (
      <div>
        Orgs
        <OrgList orgs={this.state.orgs} />
      </div>
    );
  }

  componentWillMount() {
    console.log('- Orgs componentWillMount');
    this.doSearch();
  }

  doSearch() {
    const api = '/api/orgs';
    const query = window.location.search;
    const url = `${api}?${query}`;

    axios.get(url)
    .then(({ data }) => {
      console.log(window.location.pathname, url, data);
      // TODO: checkLogin

      this.setState({ orgs: data.orgs });
    });
  }
}
