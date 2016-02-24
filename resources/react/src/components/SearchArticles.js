import React from 'react';
import Articles from './Articles';
import axios from 'axios';

export default class SearchArticles extends React.Component {
  constructor() {
    super();
    this.state = {
      articles: [],
    };
  }
  render() {
    return (
      <Articles articles={this.state.articles} />
    );
  }
  componentDidMount() {
    this.doSearch(this.props.params.keyword);
  }
  doSearch(keyword) {
    const url = '/api/article?q='+keyword;
    axios.get(url)
    .then((res) => {
      console.log('- searchArticles', keyword, res.data.articles);
      this.setState({ articles: res.data.articles });
    });
  }
}
