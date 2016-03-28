import React, { Component } from 'react';
import axios from 'axios';
import ArticleList from './Article/ArticleList';

export default class Articles extends Component {
  constructor() {
    super();
    this.state = {
      articles: [],
    };
  }

  render() {
    return (
      <div className="row">
        <div className="col-sm-3">
          Standards
        </div>
        <div className="col-sm-9">
          Articles
          <ArticleList articles={this.state.articles} />
        </div>
      </div>
    );
  }

  componentWillMount() {
    console.log('- Articles componentWillMount');
    this.doSearch();
  }

  doSearch() {
    const api = '/api/articles';
    const query = window.location.search;
    const url = `${api}?${query}`;

    axios.get(url)
    .then(({ data }) => {
      console.log(window.location.pathname, url, data);
      // TODO: checkLogin

      this.setState({ articles: data.articles });
    });
  }
}
