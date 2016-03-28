import React, { Component } from 'react';
import axios from 'axios';

import { pageList } from '../util/utils';

import ArticleList from './Article/ArticleList';
import PageList from './Page/PageList';

export default class Articles extends Component {
  constructor() {
    super();
    this.state = {
      result: {},
      articles: [],
	  pages: [],
    };
  }

  render() {
    return (
      <div className="row">
        <div className="col-sm-3">
          Standards
        </div>
        <div className="col-sm-9">
          <ArticleList
		  	result={this.state.result}
		  	articles={this.state.articles}
		 />
		 <PageList
		   pages={this.state.pages}
		 />
        </div>
      </div>
    );
  }

  componentWillMount() {
    console.log('- Articles componentWillMount');
    this.doSearch();
  }

  componentWillReceiveProps() {
    this.doSearch();
	window.$('body').animate({scrollTop:0}, '500');
  }

  doSearch() {
    const api = '/api/articles';
    const query = window.location.search;
    const url = `${api}${query}`;

    axios.get(url)
    .then(({ data }) => {
      console.log(window.location.pathname, url, data);
      // TODO: checkLogin
	  const pages = pageList(data.result.articles);

      this.setState({
        result: data.result || {},
	  	articles: data.articles || [],
		pages: pages || [],
      });
    });
  }
}
